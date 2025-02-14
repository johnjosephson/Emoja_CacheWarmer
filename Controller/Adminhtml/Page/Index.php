<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Controller\Adminhtml\Page;

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
        $resultPage->addBreadcrumb(__('Cache Warmer'), __('Cache Warmer Pages'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cache Warmer Pages'));

        return $resultPage;
    }
}
