<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Emoja\CacheWarmer\Api\Data\PageInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Page extends AbstractModel implements PageInterface
{

    public function __construct(
        private SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Page::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return (int)  $this->getData(PageInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->getData(PageInterface::URI);
    }

    /**
     * {@inheritdoc}
     */
    public function setUri($value)
    {
        return $this->setData(PageInterface::URI, $value);
    }


    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return $this->getData(PageInterface::CACHE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheId($value)
    {
        return $this->setData(PageInterface::CACHE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->getData(PageInterface::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($value)
    {
        return $this->setData(PageInterface::CONTENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return (int) $this->getData(PageInterface::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        return $this->setData(PageInterface::PRODUCT_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryId()
    {
        return (int) $this->getData(PageInterface::CATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryId($value)
    {
        return $this->setData(PageInterface::CATEGORY_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return (int)  $this->getData(PageInterface::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(PageInterface::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        return (int) $this->getData(PageInterface::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($value)
    {
        return $this->setData(PageInterface::GROUP_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setVaryData($value)
    {
        if (is_array($value)) {
            ksort($value);
            $value = $this->serializer->serialize($value);
        }

        return $this->setData(PageInterface::VARY_DATA, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryString()
    {
        $data = $this->getVaryData();
        if (!empty($data)) {
            ksort($data);
            return $this->serializer->serialize($data);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryData()
    {
        $varyData = $this->getData(PageInterface::VARY_DATA);
        $value = isset($varyData) ? $this->serializer->unserialize($varyData) : [];

        if (is_array($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(PageInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(PageInterface::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(PageInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(PageInterface::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(PageInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(PageInterface::UPDATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedAt()
    {
        return $this->getData(PageInterface::CACHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCachedAt($value)
    {
        return $this->setData(PageInterface::CACHED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWarmedAt()
    {
        return $this->getData(PageInterface::WARMED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWarmedAt($value)
    {
        return $this->setData(PageInterface::WARMED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFlushedAt()
    {
        return $this->getData(PageInterface::FLUSHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setFlushedAt($value)
    {
        return $this->setData(PageInterface::FLUSHED_AT, $value);
    }


}
