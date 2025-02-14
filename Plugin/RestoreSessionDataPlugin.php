<?php
/**
 * Emoja_CacheWarmer
 *
 * @copyright   Copyright (c) 2025 John Josephson
 * @author      johnjay@alumni.caltech.edu
 */


namespace Emoja\CacheWarmer\Plugin;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Emoja\CacheWarmer\Service\SessionService;


class RestoreSessionDataPlugin
{

    public function __construct(
        private SessionService $sessionService,
        private StoreManagerInterface $storeManager,
        private CustomerSession $customerSession,
        private CustomerCollectionFactory $customerCollectionFactory,
        private Registry $registry
    ) {

    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param \Magento\Framework\App\Request\Http             $request
     * @return void
     */
    public function beforeDispatch($subject, $request)
    {
        $sessionData = $this->sessionService->getSessionData();

        if ($sessionData) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();

            if (isset($sessionData['current_currency'])) {
                $store->setCurrentCurrencyCode($sessionData['current_currency']);
            }

            if (isset($sessionData['customer_group'])) {
                $customer = $this->customerCollectionFactory->create()
                    ->addFieldToFilter('group_id', $sessionData['customer_group'])
                    ->addFieldToFilter('store_id', $store->getId())
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();
                if ($customer) {
                    $this->customerSession->loginById($customer->getId());
                }
            }
        }
    }
}
