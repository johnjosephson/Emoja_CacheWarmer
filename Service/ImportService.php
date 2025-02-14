<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Service;

use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;

use Magento\Framework\App\Filesystem\DirectoryList;
use  Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;

class ImportService
{
    /**
     * @var array
     */
    private $cookies = [];

    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private Filesystem $filesystem,
        private StoreManagerInterface $storeManager,
        private CacheWarmerLogger $logger
    )
    {

    }

    public function importFile(string $filename, $storeId, array $groupIds = [], bool $skipHeader = true)
    {
        $this->logger->info('Cache Warmer Import - Starting');
        if (empty($groupIds)) {
            $groupIds = [0];
        }

        /** @var ReadInterface $inHandler */
        $inHandler = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->openFile($filename);
        $this->logger->info('Cache Warmer Import - Importing file ' . $filename);
        $storeUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        $rows = 0;
        while ($data = $inHandler->readCsv()) {
            try {
                if (($rows++ === 0) || empty($skipHeader)) {
                    continue;
                }
                $url = $data[0];
                $importUrl = $this->getStoreUrl($url, $storeUrl);
                foreach ($groupIds as $groupId) {
                    $this->addUrl($importUrl, $storeId, $groupId);
                }
                $rows++;
            } catch (Exception $e) {
                $this->logger->info('Cache Warmer Import - Exception: ' . $e->getMessage(), true);
                continue;
            }
        }
        $this->logger->info('Cache Warmer Import - finished importing file  ' . $filename);
    }

    public function addUrl(string $url, int $storeId, int $groupId)
    {
        $page = $this->pageRepository->create();
        $page->setUri($url);
        $page->setStoreId($storeId);
        $page->setGroupId($groupId);
        $this->pageRepository->save($page);
    }

    public function importUrl(string $url, $storeId, array $groupIds)
    {
        $storeUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        $importUrl = $this->getStoreUrl($url, $storeUrl);
        foreach ($groupIds as $groupId) {
            $this->logger->info('Cache Warmer Import - url: ' . $url . ', store id: ' . $storeId . ', group id: ' . $groupId);
            $this->addUrl($importUrl, $storeId, $groupId);
        }
    }

    private function getStoreUrl($importUrl, $storeUrl)
    {
        $parsedUrl = \Safe\parse_url($importUrl);
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        return $storeUrl . ltrim($parsedUrl['path'], '/') . $query;
    }
}
