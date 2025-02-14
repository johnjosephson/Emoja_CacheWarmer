<?php

namespace Emoja\CacheWarmer\Model\Repository;

use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Model\Page;
use Emoja\CacheWarmer\Model\PageFactory;
use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory;

class PageRepository implements PageRepositoryInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private CollectionFactory $collectionFactory,
        private PageFactory $pageFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $page = $this->create();
        $page = $this->entityManager->load($page, $id);

        if (!$page->getId()) {
            return false;
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->pageFactory->create();
    }


    /**
     * {@inheritdoc}
     */
    public function getByURIGroup(string $uri, int $customerGroupId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter("uri", $uri)
            ->addFieldToFilter("group_id", $customerGroupId)
            ->setPageSize(1);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCacheId($cacheId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter("cache_id", $cacheId)
                    ->setPageSize(1);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PageInterface $page)
    {
        $this->entityManager->delete($page);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * {@inheritdoc}
     */
    public function save(PageInterface $page)
    {
        if (!$page->getId() || !$page->getCreatedAt()) {
            //using this, because default magento function is not working correctly in some configurations
            $page->setCreatedAt(gmdate("Y-m-d H:i:s"));
        }

        $page2 = $this->getByURIGroup($page->getUri(), $page->getGroupId());

        if ($page2 && $page->getId() != $page2->getId()) {
            return;
        }

        $page->setUpdatedAt(gmdate("Y-m-d H:i:s"));

        return $this->entityManager->save($page);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param PageInterface $page
     */
    // Need this method to avoid exception "Asymmetric transaction rollback"
    // while deleting pages in the loop
    public function deletePage(PageInterface $page)
    {
        $resource = $this->getCollection()->getResource();
        $connection = $this->getCollection()->getConnection();

        $connection->query(
            "DELETE FROM " . $resource->getTable(PageInterface::TABLE_NAME)
            . " WHERE " . PageInterface::ID . " = " . $page->getId()
        );
    }
}
