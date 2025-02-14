<?php

namespace Emoja\CacheWarmer\Model\Repository;

use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;
use Magento\Framework\EntityManager\EntityManager;
use Emoja\CacheWarmer\Api\Data\LogInterface;
use Emoja\CacheWarmer\Model\LogFactory;
use Emoja\CacheWarmer\Api\Repository\LogRepositoryInterface;
use Emoja\CacheWarmer\Model\ResourceModel\Log\CollectionFactory;

class LogRepository implements LogRepositoryInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private CollectionFactory $collectionFactory,
        private LogFactory $logFactory,
        private CacheWarmerLogger $logger
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $log = $this->create();
        $log = $this->entityManager->load($log, $id);

        if (!$log->getId()) {
            return false;
        }

        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->logFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(LogInterface $log)
    {
        $this->entityManager->delete($log);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * {@inheritdoc}
     */
    public function save(LogInterface $log)
    {
        return $this->entityManager->save($log);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * {@inheritdoc}
     */
    public function savePageAction(PageInterface $page, string $action)
    {
        $log = $this->create();
        $log->setPageId($page->getId());
        $log->setAction($action);
        $log->setStatus($page->getStatus());
        return $this->save($log);
    }


    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }


    public function cleanLogs(int $days)
    {
        if ($days < 0) {
            return;
        }
        $dt = new \DateTime();
        $interval = new \DateInterval(sprintf('P%dD', $days));
        $dt->sub($interval);
        $dt->setTime(0, 0, 0);
        $collection = $this->getCollection();
        $connection = $collection->getResource()->getConnection();
        $whereSql = $connection->quoteInto('created_at < ?', $dt->format('Y-m-d H:i:s') ) ;
        $this->logger->info('Deleting cache warmer logs older than ' . $days . ' days (' . $dt->format('Y-m-d H:i:s') . ')');
        $count = $connection->delete($collection->getMainTable(), $whereSql);
        $this->logger->info($count . ' cache warmer logs deleted');
    }

}
