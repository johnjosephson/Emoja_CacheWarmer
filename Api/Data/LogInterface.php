<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Api\Data;

interface LogInterface
{
    const TABLE_NAME = 'emoja_cache_warmer_log';

    const ID = 'log_id';
    const PAGE_ID = 'page_id';
    const ACTION = 'action';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';

    const ACTION_CACHED = 'cached';
    const ACTION_PENDING = 'pending';
    const ACTION_WARMED = 'warmed';
    const ACTION_FLUSHED = 'flushed';
    const ACTION_UNCACHEABLE = 'uncacheable';
    const ACTION_DISABLED = 'disabled';
    const ACTION_ERROR = 'error';
    const ACTION_RESET = 'reset';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getPageId();

    /**
     * @param int $value
     * @return $this
     */
    public function setPageId($value);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $value
     * @return $this
     */
    public function setAction($value);

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

}
