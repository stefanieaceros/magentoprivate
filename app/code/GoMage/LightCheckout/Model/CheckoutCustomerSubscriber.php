<?php

namespace GoMage\LightCheckout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Newsletter\Model\SubscriberFactory;

class CheckoutCustomerSubscriber
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param string|null $email
     */
    public function execute($email)
    {
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $subscriber = $this->subscriberFactory->create();

        if ($isLoggedIn) {
            $customerId = $this->customerSession->getCustomerId();
            $subscriber->subscribeCustomerById($customerId);
        } else {
            if ($email !== null) {
                $subscriber->subscribe($email);
            }
        }
    }
}
