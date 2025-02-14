<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Api\Data;

interface PageInterface
{
    const TABLE_NAME = 'emoja_cache_warmer_page';

    const ID = 'page_id';
    const URI = 'uri';
    const CONTENT = 'content';
    const CACHE_ID = 'cache_id';
    const PRODUCT_ID = 'product_id';
    const CATEGORY_ID = 'category_id';
    const STORE_ID = 'store_id';
    const GROUP_ID = 'group_id';
    const VARY_DATA = 'vary_data';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CACHED_AT = 'cached_at';
    const FLUSHED_AT = 'flushed_at';
    const WARMED_AT = 'warmed_at';

    const STATUS_CACHED = 'cached';
    const STATUS_PENDING = 'pending';
    const STATUS_UNCACHEABLE = 'uncacheable';
    const STATUS_DISABLED = 'disabled';
    const STATUS_ERROR = 'error';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getUri();

    /**
     * @param string $value
     * @return $this
     */
    public function setUri($value);

    /**
     * @return string
     */
    public function getCacheId();

    /**
     * @param string $value
     * @return $this
     */
    public function setCacheId($value);


    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $value
     * @return $this
     */
    public function setContent($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     * @return $this
     */
    public function setProductId($value);

    /**
     * @return int
     */
    public function getCategoryId();

    /**
     * @param int $value
     * @return $this
     */
    public function setCategoryId($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $value
     * @return $this
     */
    public function setGroupId($value);

    /**
     * @return array
     */
    public function getVaryData();

    /**
     * @param string|array $value
     * @return $this
     */
    public function setVaryData($value);

    /**
     * @return string
     */
    public function getVaryString();


    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setStatus($value);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);


    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value);

    /**
     * @return string
     */
    public function getWarmedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setWarmedAt($value);

    /**
     * @return string
     */
    public function getCachedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCachedAt($value);

    /**
     * @return string
     */
    public function getFlushedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setFlushedAt($value);

}
