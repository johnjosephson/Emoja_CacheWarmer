<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Emoja\CacheWarmer\Api\Data\LogInterface;
use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Model\Log;
use Emoja\CacheWarmer\Model\ResourceModel\Log as LogResource;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Log::class, LogResource::class);
        $this->_idFieldName = LogInterface::ID;
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
//        $this->getSelect()->from(
//            ['e' => $this->getMainTable()],
//            [
//                'e.log_id',
//                'e.page_id',
//                'e.action',
//                'e.created_at',
//            ]
//        );
        $this->getSelect()->join(
            ['acw_page' => $this->getTable(PageInterface::TABLE_NAME)],
            'acw_page.page_id = main_table.page_id',
            [
                'store_id',
                'group_id',
                'uri',
                'cached_at',
                'warmed_at',
                'flushed_at',
            ]
        );

        return $this;
    }

}
