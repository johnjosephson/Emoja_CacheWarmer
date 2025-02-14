<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Service;

use Emoja\CacheWarmer\Model\CurlChannelFactory;
use Emoja\CacheWarmer\Model\CurlResponseFactory;
use Emoja\CacheWarmer\Service\SessionServicee;
use Emoja\CacheWarmer\Model\Config;

class RequestService
{
    /**
     * @var array
     */
    private $cookies = [];

    public function __construct(
        private CurlServiceFactory $curlServiceFactory,
        private Config $config
    ) {

    }

    /**
     * @param string $httpHeader
     * @param string $url
     * @return array
     */
    public function parseCookie($httpHeader, $url)
    {
        //convert cookie string to array
        $parts = explode(";", $httpHeader);

        $cookie = [];
        foreach ($parts as $v) {
            $v = trim($v);
            parse_str($v, $vv);
            $cookie[key($vv)] = reset($vv);
        }
        reset($cookie);
        $cookie['key'] = key($cookie);
        $cookie['value'] =  array_shift($cookie);
        ;
        if (!isset($cookie['path'])) {
            $cookie['path'] = '/';
        }
        if (!isset($cookie['domain'])) {
            $cookie['domain'] = parse_url($url, PHP_URL_HOST);
        }
        return $cookie;
    }

    /**
     * @param string $url
     * @param string $content
     */
    public function parseCookies($url, $content)
    {
        preg_match_all('/^Set-Cookie:(.*);/mi', $content, $matches);
        foreach ($matches[1] as $httpHeader) {
            $cookie = $this->parseCookie($httpHeader, $url);
            $path = $cookie['domain'].$cookie['path'];
            if (!isset($this->cookies[$path])) {
                $this->cookies[$path] = [];
            }

            if ($cookie['value'] == "deleted") {
                unset($this->cookies[$path][$cookie['key']]);
            } else {
                $this->cookies[$path][$cookie['key']] = $cookie['value'];
            }
        }
    }

    /**
     * @param string      $url
     * @param string      $sessionDataCookie
     * @param string|bool $userAgent
     *
     * @return array
     */
    public function makeRequest($url, $sessionDataCookie, $userAgent)
    {
        /** @var CurlService $curlService */
        $curlService = $this->curlServiceFactory->create();
        $channel = $curlService->initChannel();
        $channel->setUrl($url);
        $channel->setOption(CURLOPT_FOLLOWLOCATION, true);
        $channel->setOption(CURLOPT_HEADER, true);

        $channel->setUserAgent($userAgent);

        // apply cookies
        // we sort array to make sure that
        // cookies with path x.com/ are applied before cookies with path x.com/de/
        $keys = array_map('strlen', array_keys($this->cookies));
        array_multisort($keys, SORT_DESC, $this->cookies);
        foreach ($this->cookies as $domainPath => $v) {
            // if cookie domain is not .x.com
            if ($domainPath[0] !== ".") {
                $domainPath = "://".$domainPath;
            }
            if (strpos($url, $domainPath) === false) {
                continue;
            }

            foreach ($v as $k => $v2) {
                $channel->addCookie($k, $v2);
            }
        }
        if ($sessionDataCookie) {
            $channel->addCookie(SessionService::SESSION_COOKIE, $sessionDataCookie);
        }

        $response = $curlService->request($channel);

        return ['response' => $response, 'curl' => $channel->getCUrl()];
    }
}
