<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

declare(strict_types=1);

namespace Emoja\CacheWarmer\Cron;

use Emoja\CacheWarmer\Api\Repository\LogRepositoryInterface;
use Emoja\CacheWarmer\Model\Config;

class CleanLogsCron
{

    public function __construct(
        private LogRepositoryInterface $logRepository,
        private Config $config
    )
    {

    }

    public function execute(): void
    {
        $days = $this->config->getCleanLogDays();
        if ($days >= 0) {
            $this->logRepository->cleanLogs($days);
        }
    }

}
