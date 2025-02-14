<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Logger;

class CacheWarmerDebugLogHandler extends \Magento\Framework\Logger\Handler\Base
{
    private $debug;

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null
    )
    {
        $this->debug = $this->scopeConfig->isSetFlag('emoja_cachewarmer/general/debug');
        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/cache_warmer_debug.log';



    /**
     * @inheritDoc
     */
    protected function write(array $record): void
    {
        if ($this->debug) {
            parent::write($record);
        }
    }
}
