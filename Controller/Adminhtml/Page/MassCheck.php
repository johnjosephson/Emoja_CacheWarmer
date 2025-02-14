<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 */

namespace Emoja\CacheWarmer\Controller\Adminhtml\Page;

use Emoja\CacheWarmer\Api\Data\PageInterface;
use Emoja\CacheWarmer\Model\Repository\PageRepository;
use Emoja\CacheWarmer\Service\WarmerService;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Emoja\CacheWarmer\Api\Repository\PageRepositoryInterface;

/**
 * MassCheck Controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassCheck extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
     * @var PageRepository $pageRepository
     */
    private $warmerService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory $collectionFactory
     * @param PageRepositoryInterface $pageRepository
     * @param WarmerService $warmerService
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Emoja\CacheWarmer\Model\ResourceModel\Page\CollectionFactory $collectionFactory,
        PageRepositoryInterface $pageRepository,
        WarmerService $warmerService
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->pageRepository = $pageRepository;
        $this->warmerService = $warmerService;
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
        $checkedItems = 0;

        /** @var PageInterface $page */
        foreach ($collection as $page) {
            try {
                $hit = $this->warmerService->validatePageCacheId($page);
                $this->messageManager->addNotice(__('[%1] %2 - store %3 - store %4 - %5',
                    $page->getId(), $page->getUri(), $page->getStoreId(), $page->getGroupId(), ($hit ? 'HIT': 'MISS' )));
                $checkedItems++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($checkedItems != 0) {
            if ($collectionSize != $checkedItems) {
                $this->messageManager->addErrorMessage(
                    __('Failed to check %1 Page(s).', $collectionSize - $checkedItems)
                );
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 Page(s) have been checked.', $checkedItems)
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
