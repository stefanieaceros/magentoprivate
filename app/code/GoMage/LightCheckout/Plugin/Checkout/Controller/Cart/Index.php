<?php

namespace GoMage\LightCheckout\Plugin\Checkout\Controller\Cart;

use GoMage\Core\Helper\Data;
use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Setup\InstallData;
use Magento\Checkout\Controller\Cart\Index as CartIndex;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param ResultFactory $resultFactory
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->helper = $helper;
    }

    /**
     * Redirect from cart to checkout according to configuration.
     *
     * @param CartIndex $subject
     * @param \Closure $proceed
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(CartIndex $subject, \Closure $proceed)
    {
        if ($this->helper->isA(InstallData::MODULE_NAME)
            && $this->checkoutConfigurationsProvider->isLightCheckoutEnabled()
            && $this->checkoutConfigurationsProvider->getIsDisabledCart()
        ) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result = $resultForward
                ->setModule('lightcheckout')
                ->setController('index')
                ->forward('index');
        } else {
            $result = $proceed();
        }

        return $result;
    }
}
