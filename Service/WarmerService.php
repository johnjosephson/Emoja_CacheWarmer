<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Service;

use Emoja\CacheWarmer\Api\Data\LogInterface;
use Emoja\CacheWarmer\Api\Repository\LogRepositoryInterface;
use Emoja\CacheWarmer\Model\CurlResponse;
use Emoja\CacheWarmer\Model\Page;
use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\Serializer\Json;
use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Emoja\CacheWarmer\Model\Config;
use Emoja\CacheWarmer\Model\ResourceModel\Page\Collection as PageCollection;
use Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class WarmerService
{
    const XX_WARMER = 'xx__warmer';
    const USER_AGENT = 'CacheWarmer (m__warmer)';
    const STATUS_COOKIE = 'CacheWarmerStatus';
    const WARMER_UNIQUE_VALUE = 'cache_warmer/unique_value';
    const CHECK_CLEANED_PAGES = 'check_cleaned_pages';

    public function __construct(
        private RequestServiceFactory $requestServiceFactory,
        private SessionServiceFactory $sessionServiceFactory,
        private Config $config,
        private Cache $cache,
        private PageCollectionFactory $pageCollectionFactory,
        private PageRepositoryInterface $pageRepository,
        private LogRepositoryInterface $logRepository,
        private State $state,
        private StoreManagerInterface $storeManager,
        private Json $serializer,
        private CacheWarmerLogger $logger
    )
    {
    }

    public function checkPages(bool $reset = true)
    {
        // flush is called multiple times (?) so block multiple checks
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->config->isEnabled($store->getId())) {
                try {
                    // remove CACHE ID IF NOT VALID SO IT WILL BE WARMED
                    $this->checkStorePages((int)$store->getId(), $reset);
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    public function checkStorePages(int $storeId, bool $reset = true)
    {
        $this->logger->info('Page Cache Check: ' . ', store id: ' . $storeId);
        /** @var PageCollection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('cache_id', ['notnull' => true]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter('status', ['neq' => PageInterface::STATUS_DISABLED]);
        $this->logger->info($collection->getSelect()->__toString());
        /** @var PageInterface $page */
        foreach ($collection as $page) {
            $this->loadPage($page, $reset);
        }
    }

    public function loadPage(PageInterface $page, bool $reset = true)
    {
        $this->loadCacheByPage($page);
        if (!empty($page->getContent())) {
            $this->logger->debug('Page Cache Valid - page id: ' . $page->getId() . ', uri: ' . $page->getUri()
                . ', store id: ' . $page->getStoreId() . ', group id: ' . $page->getGroupId());
        } else {
            $this->logger->info('Page Cache INVALID - page id: ' . $page->getId() . ', uri: ' . $page->getUri()
                . ', store id: ' . $page->getStoreId() . ', group id: ' . $page->getGroupId());
            if ($reset) {
                $this->resetPage($page);
            }
        }
    }

    public function resetPage(PageInterface $page)
    {
        $page->setCacheId(null);
        $page->setCachedAt(null);
        $page->setWarmedAt(null);
        $page->setStatus(PageInterface::STATUS_PENDING);
        $page->setFlushedAt(gmdate("Y-m-d H:i:s"));
        $this->pageRepository->save($page);
        $this->logRepository->savePageAction($page, LogInterface::ACTION_FLUSHED);
    }

    public function cleanUrl(string $uri, int $storeId, int $groupId)
    {
        $cacheId = $this->generateCacheIdentifier($uri, $groupId, $storeId);
        $this->logger->info('Clean URL: ' . $uri . ', store id: ' . $storeId . ', group id: ' . $groupId . ', cache id: ' . $cacheId);
        $this->cache->remove($cacheId);
    }

    public function cleanPageByPageId(int $pageId)
    {
        $page = $this->pageRepository->get($pageId);
        if ($page) {
            $this->cleanPage($page);
        }
    }

    public function cleanPageByUri(string $uri, int $storeId, int $groupId)
    {
        $page = $this->pageRepository->getByURIGroup($uri, $groupId, $storeId);
        if ($page) {
            $this->cleanPage($page);
        }
    }

    /**
     * @param int $pageId
     * @return PageInterface
     */
    public function loadCacheByPageId(int $pageId)
    {
        $page = $this->pageRepository->get($pageId);
        if ($page) {
            return $this->loadCacheByPage($page);
        } else {
            return null;
        }
    }

    public function loadCacheByUri(string $uri, int $groupId)
    {
        $page = $this->pageRepository->getByURIGroup($uri, $groupId);
        if ($page) {
            return $this->loadCacheByPage($page);
        } else {
            return null;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function loadCacheByPage(PageInterface $page)
    {
        $cacheId = $page->getCacheId();
        if (!empty($cacheId)) {
            $cacheData = $this->cache->load($cacheId);
            $page->setContent($cacheData);
        }
        return $page;
    }


    /**
     * {@inheritdoc}
     */
    public function validatePageCacheId(PageInterface $page)
    {
        $pageCacheId = $page->getCacheId();
        if (!empty($pageCacheId)) {
            $cacheId = $this->generateCacheIdentifier($page->getUri(), $page->getGroupId(), $page->getStoreId());
            $this->logger->info('Page Cache ID Validate: ' . $page->getId() . ', uri: ' . $page->getUri()
                . ', store id: ' . $page->getStoreId() . ', group id: ' . $page->getGroupId());
            if ($cacheId === $pageCacheId) {
                $this->logger->info('Page Cache Match: ' . ', cache id: ' . $page->getCacheId());
                return true;
            } else {
                $this->logger->info('Page Cache NO MATCH: ' . ', cache id: ' . $page->getCacheId() . ', generated cache id: ' . $cacheId);
                return false;
            }
        }
        return false;
    }

    public function syncStatus(int $storeId, bool $reset = true)
    {
        $this->logger->info('Page Cache Sync: ' . ', store id: ' . $storeId);
        /** @var PageCollection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('cache_id', ['null' => true]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter('status', ['neq' => PageInterface::STATUS_DISABLED]);
        $this->logger->info($collection->getSelect()->__toString());
        foreach ($collection as $page) {
            $this->syncPageStatus($page, null, $reset);
        }
    }

    /**
     * Generating Cache ID and checking if the page has been cached
     *
     * {@inheritdoc}
     */
    public function syncPageStatus(PageInterface $page, $cacheId = null, bool $reset = true)
    {
        if (empty($cacheId)) {
            $cacheId = $this->generateCacheIdentifier($page->getUri(), $page->getGroupId(), $page->getStoreId());
        }
         if (!empty($cacheId)) {
            $cacheData = $this->cache->load($cacheId);
            if (!empty($cacheData)) {
                $this->logger->info('Page Cached Outside of Warmer : ' . $page->getId() . ', uri: ' . $page->getUri()
                    . ', store id: ' . $page->getStoreId() . ', group id: ' . $page->getGroupId() . ', cache id: ' . $cacheId);
                if ($reset) {
                    $this->setSyncData($page, $cacheId);
                    $this->logRepository->savePageAction($page, LogInterface::ACTION_CACHED);
                }
                return true;
            }
        }
        return false;
    }

    private function setSyncData(PageInterface $page, string $cacheId)
    {
        $szDate = gmdate("Y-m-d H:i:s");
        $page->setCacheId($cacheId);
        $page->setCachedAt($szDate);
        $page->setWarmedAt(null);
        $page->setFlushedAt(null);
        $page->setStatus(PageInterface::STATUS_CACHED);
        $this->pageRepository->save($page);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanPage(PageInterface $page)
    {
        $this->logger->info('Cleaning page id: ' . $page->getId() . ', uri: ' . $page->getUri()
            . ', store id: ' . $page->getStoreId() . ', group id: ' . $page->getGroupId());

        if ($page->getCacheId()) {
            $this->cache->remove($page->getCacheId());
        }
        $this->resetPage($page);
        return true;
    }

    public function warmPages(array $pageIds, int $maxExecutionTime = 0)
    {
        /** @var PageCollection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('page_id', ['in' => $pageIds]);
        return $this->warmCollection($collection, $maxExecutionTime);
    }

    public function warm(int $storeId, int $customerGroupId, int $count = 0, int $maxExecutionTime = 0)
    {
        /** @var PageCollection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('cache_id', ['null' => true]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter('group_id', ['eq' => $customerGroupId]);
        $collection->addFieldToFilter('status', ['neq' => PageInterface::STATUS_DISABLED]);
        $collection->setOrder('updated_at', 'ASC');
        $collection->setPageSize($count);
        $collection->setCurPage(1);
        return $this->warmCollection($collection, $maxExecutionTime);
    }

    public function reWarm(int $storeId, int $customerGroupId, int $count = 0, int $maxExecutionTime = 0)
    {
        /** @var PageCollection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter('group_id', ['eq' => $customerGroupId]);
        $collection->addFieldToFilter('status', ['neq' => PageInterface::STATUS_DISABLED]);
        $collection->setOrder('updated_at', 'ASC');
        $collection->setPageSize($count);
        $collection->setCurPage(1);
        return $this->warmCollection($collection, $maxExecutionTime);
    }

    /**
     * @param PageCollection $collection
     * @param int $maxExecutionTime
     */
    private function warmCollection(PageCollection $collection, int $maxExecutionTime = 0)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $e) {
        }
        /** @var RequestService $requestService */
        $requestService = $this->requestServiceFactory->create();
        $userAgent = UserAgents::DESKTOP_USER_AGENT;

        $startTime = time();
        $pageCount = 0;
        /** @var Page $page */
        foreach ($collection as $page) {
            $pageCount++;
            $this->warmPage($page, $requestService, $userAgent);
            $executionTime = time() - $startTime;
            if ($maxExecutionTime > 0 && $executionTime > $maxExecutionTime) {
                $this->logger->warning('Page Cache Warmer exceeded max execution time after ' . $executionTime . ' seconds');
                break;
            }
        }
        return $pageCount;
    }

    /**
     * @param PageInterface $page
     * @param RequestService|null $requestService
     * @param $sessionDataCookie
     * @param $userAgent
     */
    private function warmPage(PageInterface $page, ?RequestService $requestService = null, ?string $userAgent = null)
    {
        if (!isset($userAgent)) {
            $userAgent = UserAgents::DESKTOP_USER_AGENT;
        }
        if (!isset($requestService)) {
            $requestService = $this->requestServiceFactory->create();
        }
        if ($page->getGroupId() != 0) {
            $sessionData = [
                'customer_group' => $page->getGroupId(),
                'customer_logged_in' => 1,
            ];
            $sessionDataCookie = $this->sessionServiceFactory->create()->getSessionCookie($sessionData, 0, 0);
        } else {
            $sessionDataCookie = false;
        }

        $url = $page->getUri();
        $cacheId = $this->generateCacheIdentifier($url, $page->getGroupId(), $page->getStoreId());
        $this->logger->info('CacheWarmer warm - page id=' . $page->getId() . ', uri=' . $url . ', store id=' . $page->getStoreId() . ', group id=' . $page->getGroupId() . ', cache_Id=' . $cacheId);
        $page->setWarmedAt(gmdate("Y-m-d H:i:s"));
        $this->pageRepository->save($page);
        $result = $requestService->makeRequest($url, $sessionDataCookie, $userAgent);
        $this->logger->debug($result['curl']);
        /** @var CurlResponse $response */
        $response = $result['response'];
        // check if X-Magento-Cache-Debug:
        if ($response->isCacheHit()) {
            $this->syncPageStatus($page, $cacheId, true);
        }
        if ($response->getCode() !== 200) {
            $this->logger->warning('Response Code: ' . $response->getCode() . ', message: ' . $response->getBody());;
        }
    }

    public function generateCacheIdentifier(string $uri, int $groupId, int $storeId)
    {
        if ($groupId > 0) {
            $data = ['customer_group' => strval($groupId), 'customer_logged_in' => true];
            ksort($data);
            $varyString = $this->serializer->serialize($data);
            $varyHash = sha1($varyString);
        } else {
//            $varyString = null;
            $varyHash = null;
        }

        $parsedUri = \Safe\parse_url($uri);
        $cacheData = [
            $parsedUri['scheme'] == 'https',
            $uri,
            $varyHash
        ];
        $cacheIdentifier = sha1($this->serializer->serialize($cacheData));

        $identifierPrefix = '';
        $mageRunType = $this->config->getMageRunType($storeId);
        $identifierPrefix .= StoreManager::PARAM_RUN_TYPE . '=' . $mageRunType . '|';

        if ($mageRunType === 'website') {
            $mageRunCode = $this->storeManager->getWebsite($this->storeManager->getStore($storeId)->getWebsiteId())->getCode();
        } else {
            $mageRunCode = $this->storeManager->getStore($storeId)->getCode();
        }
        $identifierPrefix .= StoreManager::PARAM_RUN_CODE . '=' . $mageRunCode . '|';
        return $identifierPrefix . $cacheIdentifier;
    }

}
