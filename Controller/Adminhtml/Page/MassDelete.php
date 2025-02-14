<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Controller\Adminhtml\Page;

use Emoja\CacheWarmer\Model\Repository\PageRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;

/**
 * Mass-Delete Controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Emoja_CacheWarmer::Page_listing';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory;
     */
    private $collectionFactory;

    /**
     * @var PageRepository $pageRepository
     */
    private $pageRepository;

    /**
     * MassDelete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory $collectionFactory
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory $collectionFactory,
        PageRepositoryInterface $pageRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->pageRepository = $pageRepository;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $count = 0;
        foreach ($collection as $page) {
            try {
                $this->pageRepository->delete($page);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($count != 0) {
            if ($collectionSize != $count) {
                $this->messageManager->addErrorMessage(
                    __('Failed to delete %1 Page(s).', $collectionSize - $count)
                );
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 Page(s) have been deleted.', $count)
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
