<?php

/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command;

use Emoja\CacheWarmer\Service\ImportService;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class ImportCommand extends Command
{
    const FILE_NAME_OPTION = 'file-name';
    const URI_OPTION = 'uri';
    const GROUP_IDS_OPTION = 'group-ids';
    const STORE_ID_OPTION = 'store-id';

    public function __construct(
        private ImportService $importService,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('emoja:cachewarmer:import')
            ->setDescription('Import urls')
            ->addOption(
                self::FILE_NAME_OPTION,
                'f',
                InputOption::VALUE_REQUIRED,
                'File to import'
            )
            ->addOption(
                self::URI_OPTION,
                'u',
                InputOption::VALUE_REQUIRED,
                'Import single url'
            )
            ->addOption(
                self::GROUP_IDS_OPTION,
                'g',
                InputOption::VALUE_REQUIRED,
                'customer group ids to import, comma delimited',
                '0'
            )
            ->addOption(
                self::STORE_ID_OPTION,
                's',
                InputOption::VALUE_REQUIRED,
                'Store Id for import',
                '0'
            );
        \strftime('test', time());
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = (int) $input->getOption(self::STORE_ID_OPTION);
        if ($storeId === 0) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }
        $groupIds = $input->getOption(self::GROUP_IDS_OPTION);
        if (!empty($groupIds)) {
            $groupIds = explode(',', $groupIds);
        } else {
            $groupIds = [0];
        }

        $url = $input->getOption(self::URI_OPTION);
        if (!empty($url)) {
            $this->importService->importUrl($url, $storeId, $groupIds);
        }

        $fileName = $input->getOption(self::FILE_NAME_OPTION);
        if (!empty($fileName)) {
            $this->importService->importFile($fileName, $storeId, $groupIds);
        }
    }
}
