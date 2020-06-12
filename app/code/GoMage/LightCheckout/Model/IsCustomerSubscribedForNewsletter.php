<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Api\IsCustomerSubscribedForNewsletterInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class IsCustomerSubscribedForNewsletter implements IsCustomerSubscribedForNewsletterInterface
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($customerEmail)
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customerEmail);

        return $subscriber->isSubscribed();
    }
}
