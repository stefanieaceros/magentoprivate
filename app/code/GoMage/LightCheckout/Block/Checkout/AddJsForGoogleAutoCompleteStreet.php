<?php

namespace GoMage\LightCheckout\Block\Checkout;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Framework\View\Element\Template;

class AddJsForGoogleAutoCompleteStreet extends \Magento\Framework\View\Element\Template
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
    public function isEnableStreetAutoComplete()
    {
        return $this->checkoutConfigurationsProvider->isLightCheckoutEnabled()
            && $this->checkoutConfigurationsProvider->getIsEnabledAutoCompleteByStreet();
    }

    /**
     * @return string
     */
    public function getGoogleApiKey()
    {
        return $this->checkoutConfigurationsProvider->getAutoCompleteByStreetGoogleApiKey();
    }
}
