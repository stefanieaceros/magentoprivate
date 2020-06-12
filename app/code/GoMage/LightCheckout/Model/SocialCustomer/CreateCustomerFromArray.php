<?php

namespace GoMage\LightCheckout\Model\SocialCustomer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

class CreateCustomerFromArray
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @param CustomerFactory $customerFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Random $random
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerDataFactory,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        Random $random,
        AccountManagementInterface $accountManagement
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->random = $random;
        $this->accountManagement = $accountManagement;
    }

    public function execute($userDataArray)
    {
        $store = $this->storeManager->getStore();

        /** @var CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $customer->setFirstname($userDataArray['firstname'])
            ->setLastname($userDataArray['lastname'])
            ->setEmail($userDataArray['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());

        try {
            $customer = $this->customerRepository->save($customer);

            $newPasswordToken = $this->random->getUniqueHash();
            $this->accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);
        } catch (\Exception $e) {
            if ($customer->getId()) {
                $this->customerRepository->deleteById($customer->getId());
            }
            throw $e;
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create()->load($customer->getId());

        return $customer;
    }
}
