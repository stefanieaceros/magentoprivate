<?php

namespace GoMage\LightCheckout\Observer;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\CustomerLoginByEmailAndPassword;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\CustomerManagement;

class CreateCustomerBeforeSubmitCheckout implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerManagement
     */
    private $customerManagement;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var CustomerLoginByEmailAndPassword
     */
    private $customerLoginByEmailAndPassword;

    /**
     * @param Session $checkoutSession
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Quote\Model\CustomerManagement $customerManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param CustomerLoginByEmailAndPassword $customerLoginByEmailAndPassword
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        Session $checkoutSession,
        AccountManagementInterface $accountManagement,
        CustomerManagement $customerManagement,
        DataObjectHelper $dataObjectHelper,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        CustomerLoginByEmailAndPassword $customerLoginByEmailAndPassword,
        CustomerRepository $customerRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->accountManagement = $accountManagement;
        $this->customerManagement = $customerManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->customerLoginByEmailAndPassword = $customerLoginByEmailAndPassword;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $additionalData = $this->checkoutSession->getAdditionalInformation();

        if (isset($additionalData['password']) && $additionalData['password']) {
            $isEmailAvailable = $this->accountManagement->isEmailAvailable($quote->getBillingAddress()->getEmail());
            $checkoutMode = (int)$this->checkoutConfigurationsProvider->getCheckoutMode();

            if (($checkoutMode === 1 || $checkoutMode === 0) && !$isEmailAvailable) {
                $customer = $this->customerLoginByEmailAndPassword->execute(
                    $quote->getBillingAddress()->getEmail(),
                    $additionalData['password']
                );
                $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER);

                $this->assignCustomerToQuote($quote, $customer, false, $additionalData['password']);
            } elseif ($checkoutMode === 0 && $isEmailAvailable) {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);

                $customer = $quote->getCustomer();
                $dataArray = $quote->getBillingAddress()->getData();
                //this is needed for M2.6.
                unset($dataArray['id']);

                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $dataArray,
                    '\Magento\Customer\Api\Data\CustomerInterface'
                );

                $this->assignCustomerToQuote($quote, $customer, true, $additionalData['password']);
            }

            $this->checkoutSession->setAdditionalInformation(['password' => null]);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param $customer
     * @param bool $setAsDefault
     * @param string $password
     */
    private function assignCustomerToQuote(\Magento\Quote\Model\Quote $quote, $customer, $setAsDefault, $password)
    {
        $quote->setCustomerIsGuest(false)
            ->setCustomerGroupId(null)
            ->setPasswordHash($this->accountManagement->getPasswordHash($password));

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $quote->setCustomer($customer);

        // Create new customer with entered password and email
        $this->customerManagement->populateCustomerInfo($quote);

        $customerBillingData = $billingAddress->exportCustomerAddress();
        if ($setAsDefault === true) {
            $customerBillingData->setIsDefaultBilling(true);
        }

        $customerBillingData->setData('should_ignore_validation', true);

        if ($shippingAddress) {
            $customerShippingData = $shippingAddress->exportCustomerAddress();
            if ($setAsDefault === true) {
                $customerShippingData->setIsDefaultBilling(true);
            }

            $customerShippingData->setData('should_ignore_validation', true);
            $shippingAddress->setCustomerAddressData($customerShippingData);

            $quote->addCustomerAddress($customerShippingData);
        } else {
            if ($setAsDefault === true) {
                $customerBillingData->setIsDefaultShipping(true);
            }
        }

        $billingAddress->setCustomerAddressData($customerBillingData);
        $quote->addCustomerAddress($customerBillingData);

        //add customerId to addresses.
        if ($quote->getCustomerId()) {
            $customerId = $quote->getCustomerId();
            $billingAddress->setCustomerId($customerId);
            if ($shippingAddress) {
                $shippingAddress->setCustomerId($customerId);
            }
        }
    }
}
