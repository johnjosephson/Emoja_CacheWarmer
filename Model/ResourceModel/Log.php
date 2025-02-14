<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Emoja\CacheWarmer\Api\Data\LogInterface;

class Log extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(LogInterface::TABLE_NAME, LogInterface::ID);
    }

}
