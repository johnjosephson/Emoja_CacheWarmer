<?php

namespace Emoja\CacheWarmer\Model\Source;

use Emoja\CacheWarmer\Api\Data\LogInterface;

/**
 *
 */
class Action extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function __construct()
    {
    }

    public function getAllOptions()
    {
        if (empty($this->_options)) {
            $options = [];
            $actionOptions = [
                LogInterface::ACTION_WARMED,
                LogInterface::ACTION_FLUSHED,
                LogInterface::ACTION_CACHED,
                LogInterface::ACTION_PENDING,
                LogInterface::ACTION_UNCACHEABLE,
                LogInterface::ACTION_DISABLED,
                LogInterface::ACTION_RESET,
                LogInterface::ACTION_ERROR
            ];
            foreach ($actionOptions as $action) {
                $options[] = [
                    'value' => $action,
                    'label' => ucfirst($action)
                ];
            }
            $this->_options = $options;
        }
        return $this->_options;
    }
}
