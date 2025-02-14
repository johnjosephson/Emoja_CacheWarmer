<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Api\Repository;

use Emoja\CacheWarmer\Api\Data\PageInterface;

interface PageRepositoryInterface
{
    /**
     * @return \Emoja\CacheWarmer\Model\ResourceModel\Page\Collection|PageInterface[]
     */
    public function getCollection();

    /**
     * @return PageInterface
     */
    public function create();

    /**
     * @param PageInterface $page
     * @return PageInterface
     */
    public function save(PageInterface $page);

    /**
     * @param int $id
     * @return PageInterface|false
     */
    public function get($id);

    /**
     * @param string $cacheId
     * @return PageInterface|false
     */
    public function getByCacheId($cacheId);

    /**
     * @param PageInterface $page
     * @return bool
     */
    public function delete(PageInterface $page);

    /**
     * @param string $uri
     * @param int $customerGroupId
     * @param int $storeId
     * @return PageInterface $page
     */
    public function getByURIGroup(string $uri, int $customerGroupId);

}
