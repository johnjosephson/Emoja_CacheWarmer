<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

declare(strict_types=1);

namespace Emoja\CacheWarmer\Cron;

use Emoja\CacheWarmer\Service\CacheConfigurableJsonConfig;
use Emoja\CacheWarmer\Model\Config;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;


class CacheConfigurableJsonConfigCron
{
    public function __construct(
        private CacheConfigurableJsonConfig $cacheConfigurableJsonConfig,
        private StoreManager $storeManager,
        private Config $config
    )
    {
    }

    public function execute(): void
    {
        $storeCount = 0;
        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->config->isJsonConfigCacheEnabled($store->getId())) {
                $storeCount++;
            }
        }
        $maxTime = $this->config->getMaxExecutionTime();
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->config->isJsonConfigCacheEnabled($store->getId())) {
                $storeMaxTime = $maxTime / $storeCount;
                $this->cacheConfigurableJsonConfig->processConfigurables((int)$store->getId(), $storeMaxTime);
            }
        }
    }

}
