<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model;

use Emoja\CacheWarmer\Model\Logger\CacheWarmerLogger;

class CurlResponse
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $body;


    public function __construct(
        private CacheWarmerLogger $logger
    ) {

    }

    /**
     * @param CurlChannel $channel
     * @param int $code
     * @param array $headers
     * @param string $body
     */
    public function set(CurlChannel $channel, $code, array $headers, $body)
    {
        $this->url     = $channel->getUrl();
        $this->code    = $code;
        $this->headers = $headers;
        $this->body    = $body;

        if ($this->code == 200
            && preg_match('/Fatal error|Service Temporarily Unavailable|RuntimeException/', $body)) {
            $this->code = 500;
        }

        if ($this->code !== 200 && $this->body != '*') {
            $body = $this->body;
            if (strlen($body) > 500) {
                $body = substr($body, 0, 500)."...";
            }
            // Unsuccessful request and not status check request
            $this->logger->error("Curl Response Error", [
                'url'     => $this->url,
                'code'    => $this->code,
                'body'    => $body,
                'headers' => $this->headers,
                'CURL'    => $channel->getCUrl(),
            ]);
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function isCacheHit()
    {
        return (isset($this->headers['X-Magento-Cache-Debug']) && $this->headers['X-Magento-Cache-Debug'] === 'HIT');
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
