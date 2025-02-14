<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */

namespace Emoja\CacheWarmer\Plugin;

use Magento\Framework\App\Helper\Context as ContextHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Emoja\CacheWarmer\Service\PageService;
use Emoja\CacheWarmer\Model\Config;


class CollectPagePlugin
{

    private RequestInterface $request;

    /**
     * CollectPagePlugin constructor.
     * @param PageService $pageService
     * @param Registry $registry
     * @param Config $config
     * @param ContextHelper $contextHelper
     * @param ResponseInterface $response
     */
    public function __construct(
        private PageService $pageService,
        private Registry $registry,
        private Config $config,
        private ContextHelper $contextHelper,
        private ResponseInterface $response
    )
    {
        $this->request = $contextHelper->getRequest();
    }

    /**
     * @param mixed $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterRenderResult($subject, $result)
    {
        $userAgent = $this->contextHelper->getHttpHeader()->getHttpUserAgent();

        if ($this->pageService->isCanCollect($userAgent)) {
            $this->pageService->collect($this->request, $this->response);
        }

        return $result;
    }

}
