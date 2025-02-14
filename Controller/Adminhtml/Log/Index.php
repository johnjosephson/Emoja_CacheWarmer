<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Controller\Adminhtml\Log;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Emoja_CacheWarmer::main');
        $resultPage->addBreadcrumb(__('Cache Warmer'), __('Cache Warmer Logs'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cache Warmer Logs'));

        return $resultPage;
    }
}
