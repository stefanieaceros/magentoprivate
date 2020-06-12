<?php

namespace GoMage\LightCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

class DeliveryDateFromQuoteToOrder implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var OrderInterface $order */
        $order = $event->getOrder();
        
        /** @var Quote $quote */
        $quote = $event->getQuote();

        $order->setLcDeliveryDate($quote->getLcDeliveryDate());
        $order->setLcDeliveryDateTime($quote->getLcDeliveryDateTime());

        return $this;
    }
}
