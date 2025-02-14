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
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\App\PageCache\Identifier as CacheIdentifier;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Emoja\CacheWarmer\Model\Config;
use \Magento\Store\Model\StoreManagerInterface;
use Emoja\CacheWarmer\Model\ResourceModel\Page\Collection as PageCollection;
use Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;

class PageService
{
    const CLEAN_WARMED_PAGES = 'clean_warmed_pages';

    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private LogRepositoryInterface $logRepository,
        private SessionService $sessionService,
        private CacheIdentifier $cacheIdentifier,
        private CurlService $curlService,
        private PageCollectionFactory $pageCollectionFactory,
        private Registry $registry,
        private HttpContext $httpContext,
        private SerializerInterface $serializer,
        private Cache $cache,
        private Config $config,
        private StoreManagerInterface $storeManager,
        private CacheWarmerLogger $logger
    )
    {

    }

    /**
     * @param PageInterface $page
     */
    public function setUncacheableStatus(PageInterface $page)
    {
        $page->setStatus(PageInterface::STATUS_UNCACHEABLE);
        $this->pageRepository->save($page);
    }

    /**
     * @param PageInterface $page
     */
    public function setCachedStatus(PageInterface $page)
    {
        if ($page->getStatus() != PageInterface::STATUS_CACHED) {
            $page->setStatus(PageInterface::STATUS_CACHED);
            //using this, because default magento function is not working correctly in some configurations
            $page->setCachedAt(gmdate("Y-m-d H:i:s"));
            $page->setFlushedAt(null);
        }
        $this->pageRepository->save($page);
    }

    /**
     * @param PageInterface $page
     */
    public function setPendingStatus(PageInterface $page)
    {
        if ($page->getStatus() != PageInterface::STATUS_PENDING) {
            $page->setStatus(PageInterface::STATUS_PENDING);
            $page->setCachedAt(null);
            //using this, because default magento function is not working correctly in some configurations
            $page->setFlushedAt(gmdate("Y-m-d H:i:s"));
        }
        $this->pageRepository->save($page);
    }

    /**
     * {@inheritdoc}
     */
    public function isCached(PageInterface $page)
    {
        if ($page->getStatus() == PageInterface::STATUS_UNCACHEABLE) {
            return false;
        }
        if ($this->config->getCacheType() == PageCacheConfig::BUILT_IN) {
            if ($this->cache->load($page->getCacheId())) {
                $this->setCachedStatus($page);

                return true;
            }
            $this->setPendingStatus($page);

            return false;
        } else {
            $channel = $this->curlService->initChannel();

            $rule = null;

            $channel->setUrl($page->getUri());
            $channel->setUserAgent($page->getUserAgent());
            $channel->addCookie(WarmerService::STATUS_COOKIE, 1);
            $channel->addCookies($this->sessionService->getCookies($page));
            $channel->setHeaders($page->getHeaders());

            $response = $this->curlService->request($channel);

            if ($response->getBody() === '*') {
                $this->setPendingStatus($page);

                return false;
            }

            $code = $response->getCode();
            if ($code !== 200) {
                $message = "Page " . $page->getUri()
                    . " (id: " . $page->getId() . ") respond with status code "
                    . $code . ". ";

                if (in_array($code, [301, 302, 404])) {
                    $message .= "Removing page...";
                    $this->pageRepository->delete($page);
                }

                if ($this->config->isRequestLogEnabled()) {
                    $isBacktraceLogFileEnabled = $this->config->isBacktraceLogFileEnabled();

                    $body = strlen($response->getBody()) > 500
                        ? $body = substr($response->getBody(), 0, 500) . "..."
                        : $response->getBody();

                    $this->logger->debug($message, [
                        'cli' => php_sapi_name() == "cli" ? "Yes" : "No",
                        'code' => $code,
                        'body' => $body,
                        'backtrace' => $isBacktraceLogFileEnabled
                            ? \Magento\Framework\Debug::backtrace(true, false, false)
                            : null,
                    ]);
                }

                return false;
            }

            $this->setCachedStatus($page);

            return true;
        }
    }

    /**
     * Remove google gclid from URLs. Same as in default magento config for Varnish.
     *
     * @param string $uri
     *
     * @return string
     */
    public function prepareUri($uri)
    {
        $uri = preg_replace('/(.*)\\?gclid=[^&]+$/', '$1', $uri, -1);
        $uri = preg_replace('/(.*)\\?gclid=[^&]+&/', '$1?', $uri, -1);
        $uri = preg_replace('/(.*)&gclid=[^&]+/', '$1', $uri, -1);

        return $uri;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function getVaryDataString(RequestInterface $request)
    {
        //some 3rd party plugins modify vary data by using this method.
        //we need to make sure that vary data is modifed.
        $this->httpContext->getVaryString();

        $varyData = $this->prepareVaryData($this->httpContext->getData());
        if ($this->config->getCacheType() == PageCacheConfig::BUILT_IN) {
            return $varyData;
        }
        //on non-default stores, vary data is not empty
        //if we use varnish, first request goes without cookie and vary data
        //but we need to crawl it. so we clear vary data
        /** @var \Magento\Framework\App\Request\Http $request */
        if (!$request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)) {
            $varyData = $this->prepareVaryData([]);
        }

        return $varyData;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(RequestInterface $request, ResponseInterface $response)
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        if ($response->getStatusCode() && $response->getStatusCode() != 200) {
            return false;
        }

        // only collect Cache Warmer user agents
        $userAgent = $request->getHeader('User-Agent');
        if (!$this->isCanCollect($userAgent)) {
            return false;
        }

        $cacheId = $this->cacheIdentifier->getValue();
        if (!empty($cacheId)) {
            $status = PageInterface::STATUS_CACHED;
        } else {
            $status = PageInterface::STATUS_PENDING;
        }

        $groupId = 0;
        $varyDataString = $this->getVaryDataString($request);
        if (!empty($varyDataString)) {
            $varyData = $this->serializer->unserialize($varyDataString);
            if (isset($varyData['customer_group'])) {
                $groupId = (int)$varyData['customer_group'];
            }
        } else {
            $varyData = null;
        }


        $page = $this->pageRepository->getByCacheId($cacheId);
        $uri = $this->prepareUri($request->getUriString());

        $storeId = $this->storeManager->getStore()->getId();

        if (!$page && $request->getFullActionName() !== '__'
            && strpos($uri, '_=') === false) {

            /** @var PageInterface $page */
            $page = $this->ensurePage(
                $request,
                $this->pageRepository->getByURIGroup($uri, $groupId)
            );
            $page->setGroupId($groupId);
            $page->setStatus($status);
            $page->setCachedAt(gmdate("Y-m-d H:i:s"));
            $this->pageRepository->save($page);
            $this->logRepository->savePageAction($page, LogInterface::ACTION_WARMED);

        } elseif (is_object($page) && $page->getId()) {
            $page->setUri($uri)
                ->setCacheId($cacheId)
                ->setStoreId($storeId)
                ->setGroupId($groupId)
                ->setVaryData($varyData)
                ->setStatus($status);

            $this->pageRepository->save($page);
            $this->logRepository->savePageAction($page, LogInterface::ACTION_WARMED);
        }

        return true;
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public function isCanCollect($userAgent)
    {
        if (str_contains($userAgent, WarmerService::XX_WARMER)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function isValidUrl($url)
    {
        if (!str_contains($url, 'https://') === false
            && !str_contains($url, 'http://')) {
            return false;
        }
        $parsedUrl = parse_url($url);
        //Assume that URL like https://123.123.123.123 is not valid. no cetificate
        if (isset($parsedUrl['host'])
            && $parsedUrl['host']
            && !str_contains($url, 'http://')
            && filter_var($parsedUrl['host'], FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareVaryData($varyData)
    {
        if (is_array($varyData)) {
            ksort($varyData);
        }

        return $this->serializer->serialize($varyData);
    }

    /**
     * @param RequestInterface $request
     * @param bool|PageInterface $page
     * @return bool|PageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ensurePage($request, $page = false)
    {
        if (!$page) {
            $page = $this->pageRepository->create();
        }

        $uri = $this->prepareUri($request->getUriString());
        $product = $this->registry->registry('current_product');
        $category = $this->registry->registry('current_category');
        $productId = $product ? $product->getId() : 0;
        $categoryId = $category ? $category->getId() : 0;

        $page->setUri($uri)
            ->setCacheId($this->cacheIdentifier->getValue())
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setVaryData($this->getVaryDataString($request))
            ->setProductId($productId)
            ->setCategoryId($categoryId)
            ->setStatus(PageInterface::STATUS_PENDING);

        return $page;
    }

    public function cleanAllPages(int $storeId = 0)
    {
        if ($this->registry->registry(self::CLEAN_WARMED_PAGES)) {
            return;
        }

        try {
            $this->logger->info('CLEANING PAGES');
            $e = new \Exception();
            $this->logger->info($e->getTraceAsString());
            $gmdate = gmdate("Y-m-d H:i:s");
            /** @var PageCollection $collection */
            $collection = $this->pageCollectionFactory->create();
            $connection = $collection->getConnection();
            $connection->update($connection->getTableName(PageInterface::TABLE_NAME), [
                PageInterface::CACHE_ID => null,
                PageInterface::STATUS => PageInterface::STATUS_PENDING,
                PageInterface::CACHED_AT => null,
                PageInterface::WARMED_AT => null,
                PageInterface::FLUSHED_AT => $gmdate,
                PageInterface::UPDATED_AT => $gmdate
            ],
                ['status <> ?' => PageInterface::STATUS_DISABLED]
            );
        } catch (\Exception $e) {
            // THIS HAPPENS ON FIRST INSTALL AS CACHE IF FLUSHED BEFORE TABLE CREATED
            $this->logger->error($e->getMessage());
        }
        $this->registry->register(self::CLEAN_WARMED_PAGES, 1, true);
    }

}
