<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command;

use Emoja\CacheWarmer\Service\WarmerService;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class SyncCacheCommand extends Command
{
    const STORE_OPTION = 'store-id';
    const RESET_OPTION = 'reset';

    public function __construct(
        private State $state,
        private StoreManagerInterface $storeManager,
        private WarmerService $warmerService
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('emoja:cachewarmer:sync')
            ->setDescription('Warm urls')
            ->addOption(
                self::STORE_OPTION, 's',
                InputOption::VALUE_REQUIRED,
                'Warm for store id',
                0
            )
            ->addOption(
                self::RESET_OPTION, 'r',
                InputOption::VALUE_NONE,
                'Update cache id for page if valid (will then NOT warm)'
            );

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = (int)$input->getOption(self::STORE_OPTION);
        if ($storeId === 0) {
            $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        }
        $reset = (bool)$input->getOption(self::RESET_OPTION);

        $this->warmerService->syncStatus($storeId, $reset);
    }
}
