<?php

namespace GoMage\LightCheckout\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;

class Summary extends \Magento\Sales\Block\Order\Items
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Session $checkoutSession
     * @param OrderFactory $orderFactory
     * @param array $data
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        array $data = [],
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory = null
    ) {
        parent::__construct($context, $registry, $data, $itemCollectionFactory);

        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        $order = null;
        $lastRealOrderId = $this->checkoutSession->getLastRealOrderId();

        if ($lastRealOrderId) {
            $order = $this->orderFactory->create()->loadByIncrementId($lastRealOrderId);
            $this->_coreRegistry->register('current_order', $order);
        }

        return $order;
    }
}
