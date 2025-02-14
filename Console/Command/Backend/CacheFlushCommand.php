<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Console\Command\Backend;

use Magento\Backend\Console\Command\AbstractCacheTypeManageCommand;

class CacheFlushCommand extends AbstractCacheTypeManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:flush');
        $this->setDescription('Flushes cache storage used by cache type(s)');
        parent::configure();
    }

    /**
     * Flushes cache types
     *
     * @param array $cacheTypes
     * @return void
     */
    protected function performAction(array $cacheTypes)
    {
//        $this->eventManager->dispatch('adminhtml_cache_flush_all');
        $this->cacheManager->flush($cacheTypes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage()
    {
        return 'Flushed cache types:';
    }
}
