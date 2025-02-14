<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

declare(strict_types=1);

namespace Emoja\CacheWarmer\Cron;

use Emoja\CacheWarmer\Model\Config;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;
use Emoja\CacheWarmer\Service\WarmerService;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

class CheckCron
{

    public function __construct(
        private WarmerService $warmerService,
        private StoreManager $storeManager,
        private CacheWarmerLogger $logger,
        private Config $config
    )
    {

    }

    public function execute(): void
    {
        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->config->isEnabled($store->getId())) {
                try {
                    // DELETE CACHE ID IF NOT VALID SO IT WILL BE WARMED
                    $this->warmerService->checkStorePages((int)$store->getId(), true);
                    // ADD CACHE ID IF VALID - WARMED BY OTHER REQUEST
                    $this->warmerService->syncStatus((int)$store->getId(), true);
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

}
