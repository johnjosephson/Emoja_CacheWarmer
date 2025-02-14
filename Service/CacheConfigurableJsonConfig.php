<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

declare(strict_types=1);

namespace Emoja\CacheWarmer\Service;

use Emoja\Catalog\Block\Product\View\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;
use Magento\Framework\App\Cache;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\App\Emulation;

class CacheConfigurableJsonConfig
{
    public function __construct(
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private ProductRepositoryInterface $productRepository,
        private Emulation $emulation,
        private ObjectManagerInterface $objectManager,
        private CacheWarmerLogger $logger,
        private Cache $cache,
    )
    {
    }

    public function processConfigurables(int $storeId, int $maxExecutionTime = 0, array $entityIds = []): void
    {
        $startTime = time();
        try {
            $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);
            $products = $this->getConfigurableProducts($entityIds);
            foreach ($products as $product) {
                $this->cacheJsonConfig($product);
                $executionTime = time() - $startTime;
                if ($maxExecutionTime > 0 && $executionTime > $maxExecutionTime) {
                    $this->logger->warning('Json Config Cache Warmer exceeded max execution time after ' . $executionTime . ' seconds');
                    break;
                }
            }
            $this->emulation->stopEnvironmentEmulation();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function getConfigurableProducts(array $entityIds): array
    {
        $this->searchCriteriaBuilder->addFilter(ProductInterface::TYPE_ID, 'configurable', 'eq');
        if (!empty($entityIds)) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $entityIds, 'in');
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }

    private function cacheJsonConfig(Product $product): void
    {
        $configurable = $this->objectManager->create(Configurable::class);
        $configurable->setData('product', $product);
        $cacheId = $configurable->getCacheId();
        $szCache = $this->cache->load($cacheId);
        if ($szCache === false) {
            $this->logger->info(__('Generating JSON config cache for product %1 (%2), store ', $product->getSku(), $product->getEntityId(), $product->getStoreId()));
            $jsonConfig = $configurable->getJsonConfig();
            $this->logger->debug($jsonConfig);
        }
    }
}
