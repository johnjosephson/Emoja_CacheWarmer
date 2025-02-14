<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model\ResourceModel\Page;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Emoja\CacheWarmer\Api\Data\PageInterface;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Emoja\CacheWarmer\Model\Page::class,
            \Emoja\CacheWarmer\Model\ResourceModel\Page::class
        );

        $this->_idFieldName = PageInterface::ID;
    }
}
