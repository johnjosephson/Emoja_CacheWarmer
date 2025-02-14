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


class WarmCacheCommand extends Command
{

    const STORE_OPTION = 'store-id';
    const GROUP_OPTION = 'group-id';
    const PAGE_ARGUMENT = 'page-id';
    const COUNT_OPTION = 'count';

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
        $this->setName('emoja:cachewarmer:warm')
            ->setDescription('Warm urls')
            ->addArgument(
                self::PAGE_ARGUMENT,
                InputArgument::IS_ARRAY,
                'Warm for page id(s)'
            )
            ->addOption(
                self::GROUP_OPTION, 'g',
                InputOption::VALUE_REQUIRED,
                'Warm for group id',
                0
            )
            ->addOption(
                self::STORE_OPTION, 's',
                InputOption::VALUE_REQUIRED,
                'Warm for store id',
                0
            )
            ->addOption(
                self::COUNT_OPTION, 'c',
                InputOption::VALUE_REQUIRED,
                'Count, 0 = all uncached urls',
                0
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = (int) $input->getOption(self::STORE_OPTION);
        if ($storeId === 0) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
        }
        $groupId = (int) $input->getOption(self::GROUP_OPTION);
        $pageIds = $input->getArgument(self::PAGE_ARGUMENT);
        $count = (int) $input->getOption(self::COUNT_OPTION);

        if (!empty($pageIds)) {
            $this->warmerService->warmPages($pageIds);
        } else {
            $this->warmerService->warm($storeId, $groupId, $count);
        }
    }
}
