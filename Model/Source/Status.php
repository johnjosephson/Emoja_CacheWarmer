<?php

namespace Emoja\CacheWarmer\Model\Source;

use Emoja\CacheWarmer\Api\Data\PageInterface;

/**
 * Customer tax class source model.
 */
class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function __construct()
    {
    }

    /**
     * Retrieve all customer tax classes as an options array.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (empty($this->_options)) {
            $options = [];
            $statusOptions = [
                PageInterface::STATUS_CACHED,
                PageInterface::STATUS_PENDING,
                PageInterface::STATUS_UNCACHEABLE,
                PageInterface::STATUS_DISABLED,
                PageInterface::STATUS_ERROR
            ];
            foreach ($statusOptions as $status) {
                $options[] = [
                    'value' => $status,
                    'label' => ucfirst($status),
                ];
            }
            $this->_options = $options;
        }
        return $this->_options;
    }
}
