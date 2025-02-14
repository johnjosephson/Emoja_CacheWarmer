<?php


namespace Emoja\CacheWarmer\Model;

use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Model\CurlResponse;

class PageWarmStatus
{
    /**
     * @var PageInterface
     */
    private $page;

    /**
     * @var CurlResponse
     */
    private $response;

    /**
     * PageWarmStatus constructor.
     * @param PageInterface $page
     * @param CurlResponse $response
     */
    public function __construct(PageInterface $page, CurlResponse $response)
    {
        $this->page     = $page;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return '#' . $this->page->getId() . ' ' . $this->response->getCode() . ' ' . $this->page->getUri();
    }

    /**
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->response->getCode() !== 200;
    }

    /**
     * @return bool
     */
    public function isSoftError()
    {
        return in_array($this->response->getCode(), [404, 301, 302]);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->response->getCode();
    }
}
