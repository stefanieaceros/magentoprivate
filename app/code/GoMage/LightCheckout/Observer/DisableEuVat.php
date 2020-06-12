<?php

namespace GoMage\LightCheckout\Observer;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DisableEuVat implements ObserverInterface
{
    /**
     * @var ResourceModelConfig
     */
    private $modelConfig;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceModelConfig $modelConfig
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResourceModelConfig $modelConfig,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->modelConfig = $modelConfig;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $isEnabledVat = $this->checkoutConfigurationsProvider->getIsEnabledVatTax();
        $isVisibleVat = $this->scopeConfig->getValue(Address::XML_PATH_VAT_FRONTEND_VISIBILITY);

        if ($isEnabledVat && !$isVisibleVat) {
            $this->modelConfig->saveConfig(
                CheckoutConfigurationsProvider::XML_PATH_LIGHT_CHECKOUT_VAT_TAX_ENABLE,
                $isVisibleVat,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
    }
}
