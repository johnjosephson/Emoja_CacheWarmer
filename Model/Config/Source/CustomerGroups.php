<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */
namespace Emoja\CacheWarmer\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Model\Group;


class CustomerGroups implements \Magento\Framework\Option\ArrayInterface
{

    protected $values = [];

    public function __construct(
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->values)) {
            $collection = $this->collectionFactory->create();
            /** @var Group $customerGroup */
            foreach ($collection->getItems() as $customerGroup) {
                $this->values[] = ['value' => $customerGroup->getId(), 'label' => $customerGroup->getCustomerGroupCode()];
            }
        }
        return $this->values;
    }
}
