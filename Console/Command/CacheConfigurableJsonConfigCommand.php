<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command;

use Emoja\CacheWarmer\Service\CacheConfigurableJsonConfig;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class CacheConfigurableJsonConfigCommand extends Command
{
    const STORE_OPTION = 'store-id';
    const ENTITY_ID_ARGUMENT = 'entity-id';

    public function __construct(
        private CacheConfigurableJsonConfig $cacheConfigurableJsonConfig,
        private State $state,
        private StoreManagerInterface $storeManager
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('emoja:cachewarmer:cacheconfigurablejson')
            ->setDescription('Cache configurable json')
            ->addOption(
                self::STORE_OPTION, 's',
                InputOption::VALUE_REQUIRED,
                'Cache json for for store id',
                0
            )
            ->addArgument(
                self::ENTITY_ID_ARGUMENT,
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'entity ids to cache json for'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = (int)$input->getOption(self::STORE_OPTION);
        if ($storeId === 0) {
            $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        }
        $entityIds = $input->getArgument(self::ENTITY_ID_ARGUMENT);
        $this->state->setAreaCode(Area::AREA_FRONTEND);
        $this->cacheConfigurableJsonConfig->processConfigurables($storeId, 0, $entityIds);
    }
}
