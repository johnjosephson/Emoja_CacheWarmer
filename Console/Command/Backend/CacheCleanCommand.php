<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */


namespace Emoja\CacheWarmer\Console\Command\Backend;

use Magento\Backend\Console\Command\AbstractCacheTypeManageCommand;

class CacheCleanCommand extends AbstractCacheTypeManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:clean');
        $this->setDescription('Cleans cache type(s)');
        parent::configure();
    }

    /**
     * Cleans cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function performAction(array $cacheTypes)
    {
//        $this->eventManager->dispatch('adminhtml_cache_flush_system');
        $this->cacheManager->clean($cacheTypes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage()
    {
        return 'Cleaned cache types:';
    }
}
