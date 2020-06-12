<?php

namespace GoMage\LightCheckout\Block\Checkout;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;

/**
 * Class ConfigurationProcessor
 * @package GoMage\LightCheckout\Block\Checkout
 */
class ConfigurationProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * StyleTypeLayoutProcessor constructor.
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    )
    {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    public function process($jsLayout)
    {
        if (!isset($jsLayout['components']['checkout']['configuration'])) {
            $jsLayout['components']['checkout']['configuration'] = [
                'is3ColumnType' => (bool)$this->checkoutConfigurationsProvider->getIsShown3ColumnCheckout(),
                'comment_order' => $this->checkoutConfigurationsProvider->getCommentOrderConfig()
            ];
        }
        return $jsLayout;
    }
}
