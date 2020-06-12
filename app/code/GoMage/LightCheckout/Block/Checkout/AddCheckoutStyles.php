<?php

namespace GoMage\LightCheckout\Block\Checkout;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Framework\View\Element\Template;

class AddCheckoutStyles extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @param Template\Context $context
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @return bool
     */
    public function getIsEnabledLightCheckout()
    {
        return $this->checkoutConfigurationsProvider->isLightCheckoutEnabled();
    }

    /**
     * @return bool
     */
    public function getPlaceOrderButtonColor()
    {
        return $this->checkoutConfigurationsProvider->getCheckoutColorSettingsPlaceOrderButton();
    }

    /**
     * @return bool
     */
    public function getPlaceOrderButtonHoverColor()
    {
        return $this->checkoutConfigurationsProvider->getCheckoutColorSettingsPlaceOrderButtonHover();
    }

    /**
     * @return bool
     */
    public function getCheckoutColor()
    {
        return $this->checkoutConfigurationsProvider->getCheckoutColorSettingsCheckoutColor();
    }
}
