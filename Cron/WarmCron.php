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

class WarmCron
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
        $warmCount = (int)$this->config->getWarmCount();
        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            if ($this->config->isEnabled($storeId)) {
                // DELETE CACHE ID IF NOT VALID SO IT WILL BE WARMED
                $this->warmerService->checkStorePages((int)$store->getId(), true);
                // ADD CACHE ID IF VALID - WARMED BY OTHER REQUEST
                $this->warmerService->syncStatus((int)$store->getId(), true);
                $customerGroups = $this->config->getCustomerGroups($storeId);
                $maxExecutionTime = $this->config->getMaxExecutionTime();;
                foreach ($customerGroups as $groupId) {
                    try {
                        $this->logger->info('CacheWarmer WarmCron: warming start - store id=' . $storeId . ', customer group=' . $groupId . ', count=' . $warmCount);
                        $pageCount = $this->warmerService->warm(
                            (int)$storeId,
                            (int)$groupId,
                            (int)$warmCount,
                            (int)$maxExecutionTime
                        );
                        $this->logger->info('CacheWarmer WarmCron: warming complete with ' . $pageCount . ' pages warmed -store id=' . $storeId . ', customer group=' . $groupId . ', count=' . $warmCount);
                        if ($pageCount == 0) {
                            $this->logger->info('CacheWarmer WarmCron: no pages warmed re-warming pages - store id=' . $storeId . ', customer group=' . $groupId . ', count=' . $warmCount);
                            $pageCount = $this->warmerService->reWarm(
                                (int)$storeId,
                                (int)$groupId,
                                (int)$warmCount,
                                (int)$maxExecutionTime
                            );
                            $this->logger->info('CacheWarmer WarmCron: rewarming complete with ' . $pageCount . ' pages rewarmed -store id=' . $storeId . ', customer group=' . $groupId . ', count=' . $warmCount);
                        }
                    } catch (\Throwable $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
            }
        }
    }

}
