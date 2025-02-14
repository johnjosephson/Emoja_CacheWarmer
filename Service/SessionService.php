<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Service;

use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Service\Curl\CurlResponse;
use Emoja\CacheWarmer\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;


class SessionService
{
    const SESSION_COOKIE   = 'CacheWarmer';
    const VARY_BEGIN_TAG  = 'varu_begin_';
    const VARY_END_TAG  = '_varu_end';
    const PRODUCT_BEGIN_TAG  = 'prod_id_begin_';
    const PRODUCT_END_TAG    = '_prod_id_end';
    const CATEGORY_BEGIN_TAG = 'cat_id_begin_';
    const CATEGORY_END_TAG   = '_cat_id_end';

    /**
     * @var null|string $userAgentFirstPart
     */
    private static $userAgentFirstPart = null;


    public function __construct(
       private  Config $config,
        private \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        private SerializerInterface $serializer
    ) {

    }


    /**
     * @param array $varyData
     * @param int   $productId
     * @param int   $categoryId
     * @return string
     */
    public function getSessionCookie($varyData, $productId, $categoryId)
    {
        $agent = "";
        $agent .= $this->getUserAgentFirstPart();
        $agent .= base64_encode($this->serializer->serialize($varyData));
//        $agent .= SessionService::VARY_BEGIN_TAG . base64_encode($this->serializer->serialize($varyData))
//            . SessionService::VARY_END_TAG;
//        $agent .= SessionService::PRODUCT_BEGIN_TAG
//            . $productId . SessionService::PRODUCT_END_TAG;
//        $agent .= SessionService::CATEGORY_BEGIN_TAG
//            . $categoryId . SessionService::CATEGORY_END_TAG;

        return $agent;
    }

    /**
     * @return null|string
     */
    private function getUserAgentFirstPart()
    {
        if (self::$userAgentFirstPart === null) {
            self::$userAgentFirstPart = WarmerService::USER_AGENT . $this->getWarmerUniquePart() . ':';
        }

        return self::$userAgentFirstPart;
    }

    /**
     * Create unique warmer user agent for security reason
     * @return string
     */
    private function getWarmerUniquePart()
    {
        return $this->config->getWarmerUniquePart();
    }


    /**
     * @return string|null
     */
    public function getSessionDataFromCookie()
    {
        return $this->cookieManager->getCookie(SessionService::SESSION_COOKIE);
    }

    /**
     * We use this session data to restore enviroment during warming
     *
     * @return bool|array
     */
    public function getSessionData()
    {
        $agent = $this->getSessionDataFromCookie();
        if ($agent && strpos($agent, $this->getUserAgentFirstPart()) !== false) {
            $data = substr($agent, strpos($agent, $this->getUserAgentFirstPart()) + strlen($this->getUserAgentFirstPart()));
            $data = $this->serializer->unserialize(base64_decode($data));

            return $data;
        }

        return false;
    }

    /**
     * @return bool|array
     */
    public function getProductId()
    {
//        $agent = $this->getSessionDataFromCookie();
//        if ($agent && strpos($agent, $this->getUserAgentFirstPart()) !== false) {
//            preg_match('/' . SessionService::PRODUCT_BEGIN_TAG
//                . '(.*?)' . SessionService::PRODUCT_END_TAG . '/ims', $agent, $data);
//
//            return (isset($data[1])) ? $data[1] : false;
//        }

        return false;
    }

    /**
     * @return bool|array
     */
    public function getCategoryId()
    {
//        $agent = $this->getSessionDataFromCookie();
//        if ($agent && strpos($agent, $this->getUserAgentFirstPart()) !== false) {
//            preg_match('/' . SessionService::CATEGORY_BEGIN_TAG
//                . '(.*?)' . SessionService::CATEGORY_END_TAG . '/ims', $agent, $data);
//
//            return (isset($data[1])) ? $data[1] : false;
//        }

        return false;
    }


    /**
     * @param PageInterface $page
     * @return array
     */
    public function getCookies(PageInterface $page)
    {
        $cookies = [];
        if ($page->getCookie()) {
            $cooks = explode(";", $page->getCookie());
            foreach ($cooks as $c) {
                $cc = explode("=", $c);
                $cookies[$cc[0]] = $cc[1];
            }
        } elseif ($page->getVaryString()) {
            $cookies['X-Magento-Vary'] = $page->getVaryString();
        }

        $sessionCookie = $this->getSessionCookie(
            $page->getVaryData(),
            $page->getProductId(),
            $page->getCategoryId()
        );
        $cookies[SessionService::SESSION_COOKIE] = $sessionCookie;
        return $cookies;
    }
}
