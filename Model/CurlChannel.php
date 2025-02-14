<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Model;

class CurlChannel
{
    const DEFAULT_CURL_TIMEOUT = 60;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $cookies = [];

    public function __construct(
        private Config $config
    )
    {

        $curlTimeout = (int)$this->config->getCurlTimeout();
        $curlTimeout = ($curlTimeout > 0) ? $curlTimeout : self::DEFAULT_CURL_TIMEOUT;
        $this->options = [
            CURLOPT_HTTPGET => 1,
            CURLOPT_TIMEOUT => $curlTimeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];
//
//        if ($this->config->getHttpAuthUsername() && $this->config->getHttpAuthPassword()) {
//            $userPwd = "{$this->config->getHttpAuthUsername()}:{$this->config->getHttpAuthPassword()}";
//
//            $this->options[CURLOPT_USERPWD] = $userPwd;
//        }
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->options[CURLOPT_URL] = $url;

        return $this;
    }

    /**
     * @param string $code
     * @param string|bool $value
     * @return $this
     */
    public function setOption($code, $value)
    {
        $this->options[$code] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return isset($this->options[CURLOPT_URL]) ? $this->options[CURLOPT_URL] : false;
    }

    /**
     * @param string $agent
     * @return $this
     */
    public function setUserAgent($agent)
    {
        $this->options[CURLOPT_USERAGENT] = $agent;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $h = [];
        foreach ($headers as $key => $value) {
            $h[] = "$key: $value";
        }

        $this->options[CURLOPT_HTTPHEADER] = $h;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addCookie($name, $value)
    {
        $this->cookies[$name] = $value;
    }

    /**
     * @param array $cookies
     */
    public function addCookies($cookies)
    {
        foreach ($cookies as $k => $v) {
            $this->cookies[$k] = $v;
        }
    }

    /**
     * @return false|resource
     */
    public function getCh()
    {
        $ch = curl_init();

        if (count($this->cookies)) {
            $cookies = [];
            foreach ($this->cookies as $key => $value) {
                $cookies[] = "{$key}={$value}";
            }
            curl_setopt($ch, CURLOPT_COOKIE, implode(";", $cookies));
        }

        foreach ($this->options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        return $ch;
    }

    /**
     * @return string
     */
    public function getCurl()
    {
        $opt = [];

        $aliases = [
            CURLOPT_USERPWD => '--user',
            CURLOPT_TIMEOUT => '-m',
            CURLOPT_USERAGENT => '-A',
            CURLOPT_HTTPHEADER => '-H',
        ];

        foreach ($aliases as $option => $cOption) {
            if (isset($this->options[$option])) {
                if (is_array($this->options[$option])) {
                    foreach ($this->options[$option] as $op) {
                        $opt[] = "$cOption '" . $op . "'";
                    }
                } else {
                    $opt[] = "$cOption '" . $this->options[$option] . "'";
                }
            }
        }

        $cookie = '';
        if (count($this->cookies)) {
            $cookies = [];
            foreach ($this->cookies as $key => $value) {
                $cookies[] = "{$key}={$value}";
            }
            $cookie = '--cookie \'' . implode(";", $cookies) . '\'';
        }

        return 'curl ' . implode(' ', $opt) . ' ' . $cookie . ' -k \'' . $this->options[CURLOPT_URL] . '\'';
    }
}
