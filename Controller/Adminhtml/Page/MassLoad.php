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
 * MassLoad Controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassLoad extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
    )
    {
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

        $loaded = 0;
        $skipped = 0;
        try {
            /** @var PageInterface $page */
            foreach ($collection as $page) {
                if (!empty($page->getCacheId())) {
                    $this->warmerService->loadCacheByPage($page);
                    $loaded++;
                } else {
                    $skipped++;
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 Page(s) have been loaded, %2 have been skipped.', $loaded, $skipped)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
