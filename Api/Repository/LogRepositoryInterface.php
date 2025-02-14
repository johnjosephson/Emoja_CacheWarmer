<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Api\Repository;

use Emoja\CacheWarmer\Api\Data\LogInterface;
use Emoja\CacheWarmer\Api\Data\PageInterface;

interface LogRepositoryInterface
{

    /**
     * @return \Emoja\CacheWarmer\Model\ResourceModel\Log\Collection|LogInterface[]
     */
    public function getCollection();

    /**
     * @return LogInterface
     */
    public function create();

    /**
     * @param LogInterface $log
     * @return LogInterface
     */
    public function save(LogInterface $log);


    /**
     * @param PageInterface $log
     * @param string $action
     * @return LogInterface
     */
    public function savePageAction(PageInterface $page, string $action);


    /**
     * @param int $id
     * @return LogInterface|false
     */
    public function get($id);

    /**
     * @param LogInterface $log
     * @return bool
     */
    public function delete(LogInterface $log);


    /**
     * @param int $days
     * @return void
     */
    public function cleanLogs(int $days);

}
