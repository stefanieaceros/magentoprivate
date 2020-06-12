<?php

namespace GoMage\LightCheckout\Plugin\Quote;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Quote\Model\ChangeQuoteControl;
use Magento\Quote\Model\Quote;

/**
 * This is needed as when we creating customer during checkout we begin request as guest but later we set customer to
 * quote, Magento check than this is guest and quote has customer id and block order placing.
 */
class SimulateIsAllowedWhenCreatingCustomer
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @param ChangeQuoteControl $subject
     * @param \Closure $proceed
     * @param Quote $quote
     *
     * @return bool
     */
    public function aroundIsAllowed(
        ChangeQuoteControl $subject,
        \Closure $proceed,
        Quote $quote
    ) {
        $checkoutMode = (int)$this->checkoutConfigurationsProvider->getCheckoutMode();
        $isLightCheckoutEnable = $this->checkoutConfigurationsProvider->isLightCheckoutEnabled();

        if ($checkoutMode === 0 && $isLightCheckoutEnable) {
            return true;
        }

        return $proceed($quote);
    }
}
