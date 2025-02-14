<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command;

use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Service\WarmerService;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class CheckCacheCommand extends Command
{
    const STORE_OPTION = 'store-id';
    const PAGE_ID_OPTION = 'page-id';
    const URI_OPTION = 'uri';
    const GROUP_OPTION = 'group-id';
    const RESET_OPTION = 'reset';
    const ALL_OPTION = 'all';

    public function __construct(
        private StoreManagerInterface $storeManager,
        private WarmerService $warmerService
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('emoja:cachewarmer:check')
            ->setDescription('Check urls')
            ->addOption(
                self::URI_OPTION, 'u',
                InputOption::VALUE_REQUIRED,
                'Uri to clean'
            )
            ->addOption(
                self::PAGE_ID_OPTION, 'p',
                InputOption::VALUE_REQUIRED,
                'Page ID to clean'
            )
            ->addOption(
                self::STORE_OPTION, 's',
                InputOption::VALUE_REQUIRED,
                'Warm for store id',
                0
            )
            ->addOption(
                self::RESET_OPTION, 'r',
                InputOption::VALUE_NONE,
                'Remove cache id for page if not valid (will then warm)'
            )
            ->addOption(
                self::ALL_OPTION, 'A',
                InputOption::VALUE_NONE,
                'Check all pages'
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
        $storeId = (int)$input->getOption(self::STORE_OPTION);
        if ($storeId === 0) {
            $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        }
        $pageId = (int)$input->getOption(self::PAGE_ID_OPTION);
        $uri = $input->getOption(self::URI_OPTION);
        $reset = (bool)$input->getOption(self::RESET_OPTION);
        $all = (bool)$input->getOption(self::ALL_OPTION);
        try {
            /** @var PageInterface $page */
            if (!empty($pageId)) {
                $page = $this->warmerService->loadCacheByPageId($pageId);
            } else if (!empty($uri)) {
                $groupId = $input->getOption(self::GROUP_OPTION);
                $page = $this->warmerService->loadCacheByUri($uri, $groupId);
            } else  if ($all) {
                $this->warmerService->checkPages($reset);
                return;
            }
            if ($page === null) {
                $output->writeln('PAGE NOT FOUND');
            } else if (empty($page->getCacheId())) {
                $output->writeln('PAGE NOT CACHED');
            } else if (!empty($page->getCacheId()) && empty($page->getContent())) {
                $output->writeln('PAGE CACHE INVALID');
                if ($reset) {
                    $output->writeln('Resetting page');
                    $this->warmerService->resetPage($page);
                }
            } else if (!empty($page->getCacheId()) && !empty($page->getContent())) {
                $output->writeln('PAGE CACHED');
            } else {
                $output->writeln('PAGE CACHE UNKNOWN STATUS');
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }


    }
}
