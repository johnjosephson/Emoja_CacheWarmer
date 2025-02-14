<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */


namespace Emoja\CacheWarmer\Ui\Component\Listing;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Emoja\CacheWarmer\Model\ResourceModel\Log\CollectionFactory;
use Emoja\CacheWarmer\Model\ResourceModel\Log\Collection;

class LogDataProvider extends AbstractDataProvider
{

    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
