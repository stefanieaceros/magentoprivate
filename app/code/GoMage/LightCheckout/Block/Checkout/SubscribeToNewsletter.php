<?php

namespace GoMage\LightCheckout\Block\Checkout;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\NewsletterCheckbox;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

class SubscribeToNewsletter extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        SubscriberFactory $subscriberFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Retrieve current email address
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmailAddress()
    {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    /**
     * @return string
     */
    public function getSubscribeToNewsletterUrl()
    {
        return $this->getUrl('lightcheckout/subscribeToNewsletter/index');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        $isEnabled = (int)$this->checkoutConfigurationsProvider->getIsEnabledSubscribeToNewsletter();
        $isCustomerLogin = $this->customerSession->isLoggedIn();
        $customerId = $this->customerSession->getCustomerId();
        $isSubscribed = $this->subscriberFactory->create()->loadByCustomerId($customerId);

        if (
            $isEnabled == NewsletterCheckbox::NEWSLETTER_CHECKBOX_DISABLE
            || $isEnabled == NewsletterCheckbox::NEWSLETTER_CHECKBOX_ENABLE_IN_CHECKOUT
            || ($isCustomerLogin && $isSubscribed->getStatus() == Subscriber::STATUS_SUBSCRIBED)) {

            return '';
        }

        return parent::toHtml();
    }
}
