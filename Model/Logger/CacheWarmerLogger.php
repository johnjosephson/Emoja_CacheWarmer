<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model\Logger;


use DateTimeZone;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CacheWarmerLogger extends \Magento\Framework\Logger\Monolog
{

    private $debug;

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    )
    {
        parent::__construct($name, $handlers, $processors, $timezone);
        $this->debug = $this->scopeConfig->isSetFlag('emoja_cachewarmer/general/debug');
    }

    public function debug($message, array $context = []): void
    {
        if ($this->debug) {
            $this->addRecord(static::DEBUG, (string)$message, $context);
        }
    }
}
