<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command;

use Emoja\CacheWarmer\Service\WarmerService;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class CleanCacheCommand extends Command
{
    const URI_OPTION = 'uri';
    const GROUP_OPTION = 'group-id';
    const STORE_ID_OPTION = 'store-id';

    public function __construct(
        private State $state,
        private WarmerService $warmerService
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('emoja:cachewarmer:cleancache')
            ->setDescription('Warm urls')
            ->addOption(
                self::URI_OPTION, 'u',
                InputOption::VALUE_REQUIRED,
                'Uri to clean'
            )
            ->addOption(
                self::STORE_ID_OPTION, 's',
                InputOption::VALUE_REQUIRED,
                'Store ID to clean'
            )
            ->addOption(
                self::GROUP_OPTION, 'g',
                InputOption::VALUE_REQUIRED,
                'customer group id for uri to clean',
                0
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $uri = $input->getOption(self::URI_OPTION);
        $storeId = (int) $input->getOption(self::STORE_ID_OPTION);
        $groupId = (int) $input->getOption(self::GROUP_OPTION);
        $this->warmerService->cleanUrl($uri, $storeId, $groupId);
    }
}
