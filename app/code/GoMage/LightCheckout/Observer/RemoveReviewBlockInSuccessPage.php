<?php

namespace GoMage\LightCheckout\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Remove blocks from success page according the config settings.
 */
class RemoveReviewBlockInSuccessPage implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $orderItemsBlock = $layout->getBlock('order_items_success_page');
        $titleBlock = $layout->getBlock('order_review_title');

        $isShowBlock = $this->scopeConfig->getValue(
            'gomage_light_checkout_configuration/general/show_order_summary_on_success_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($orderItemsBlock) {
            if (!$isShowBlock) {
                $layout->unsetElement('order_items_success_page');
            }
        }

        if ($titleBlock) {
            if (!$isShowBlock) {
                $layout->unsetElement('order_review_title');
            }
        }
    }
}
