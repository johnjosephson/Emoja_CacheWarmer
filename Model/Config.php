<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model;

use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Emoja\CacheWarmer\Service\WarmerService;

class Config
{
    const DATA_VERSION = 1;

    const PERFORMANCE_CUSTOM = 0;
    const PERFORMANCE_LOW = 1;
    const PERFORMANCE_MEDIUM = 2;
    const PERFORMANCE_HIGH = 3;

    const CONFIG_ENABLED = 'emoja_cachewarmer/general/enabled';
    const CONFIG_WARM_GROUPS = 'emoja_cachewarmer/general/warm_groups';
    const CONFIG_CURL_TIMEOUT = 'emoja_cachewarmer/general/curl_timeout';
    const CONFIG_MAX_EXECUTION_TIME = 'emoja_cachewarmer/cron/max_execution_time';
    const CONFIG_WARM_COUNT = 'emoja_cachewarmer/cron/warm_count';
    const CONFIG_MAGE_RUN_TYPE = 'emoja_cachewarmer/general/mage_run_type';
    const CONFIG_DEBUG = 'emoja_cachewarmer/general/debug';
    const CONFIG_CLEAN_LOGS_DAYS = 'emoja_cachewarmer/cron/clean_logs_days';
    const CONFIG_CACHE_CONFIG_JSON = 'emoja_cachewarmer/general/cache_config_json';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private Filesystem $filesystem,
        private CacheStateInterface $cacheState,
        private TimezoneInterface $timezone,
        private SerializerInterface $serializer
    )
    {
    }

    /**
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::CONFIG_ENABLED, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * @return bool
     */
    public function isJsonConfigCacheEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(self::CONFIG_CACHE_CONFIG_JSON, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getCustomerGroups($storeId = null)
    {
        return $this->splitConfiguration(
            $this->scopeConfig->getValue(self::CONFIG_WARM_GROUPS,
                ScopeInterface::SCOPE_STORES,
                $storeId)
        );
    }

    /**
     * @return int
     */
    public function getCurlTimeout()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_CURL_TIMEOUT);
    }

    /**
     * @return int
     */
    public function getMaxExecutionTime()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_MAX_EXECUTION_TIME);
    }

    /**
     * @return int
     */
    public function getWarmCount()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_WARM_COUNT);
    }

    /**
     * @return string
     */
    public function getMageRunType($storeId)
    {
        return $this->scopeConfig->getValue(self::CONFIG_MAGE_RUN_TYPE,
            ScopeInterface::SCOPE_STORES,
            $storeId);
    }

    /**
     * @return bool
     */
    public function isPageCacheEnabled()
    {
        return $this->cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }

    /**
     * @return int
     */
    public function getCleanLogDays():int
    {
        return (int) $this->scopeConfig->getValue(self::CONFIG_CLEAN_LOGS_DAYS);
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->timezone->date();
    }

    /**
     * @return string
     */
    public function getWarmerUniquePart()
    {
        return $this->scopeConfig->getValue(
            WarmerService::WARMER_UNIQUE_VALUE,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return string
     */
    public function getCacheType()
    {
        return $this->scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TYPE);
    }

    /**
     * @return int
     */
    public function getCacheTtl()
    {
        return $this->scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TTL);
    }

    /**
     * @return string
     */
    public function getTmpPath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();

        return $path;
    }


    /**
     * @param null $store
     * @return bool
     */
    public function isStoreCodeToUrlEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isDebug($storeId)
    {
        return $this->scopeConfig->getValue(self::CONFIG_DEBUG, ScopeInterface::SCOPE_STORES, $storeId);
    }

    /**
     * @param $value string
     * @param $pattern string
     * @return array
     */
    public function splitConfiguration($value, $pattern = '/[\s,]+/')
    {
        $value = $value ?? '';
        $splitValue = preg_split($pattern, $value);
        if (empty($splitValue) || $splitValue === false) {
            $splitValue = [];
        }
        // preg_split on "" returns 0 => "", also issues with leading spaces/commas
        $parts = [];
        foreach ($splitValue as $index => $part) {
            if (strlen($part) != 0) {
                $parts[] = $part;
            }
        }
        return $parts;
    }
}
