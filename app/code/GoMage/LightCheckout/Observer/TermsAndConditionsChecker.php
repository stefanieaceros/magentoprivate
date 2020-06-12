<?php

namespace GoMage\LightCheckout\Observer;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TermsAndConditionsChecker implements ObserverInterface
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
     * @param ResourceModelConfig $modelConfig
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     */
    public function __construct(
        ResourceModelConfig $modelConfig,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    ) {
        $this->modelConfig = $modelConfig;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $isEnabledTAC = $this->checkoutConfigurationsProvider->getIsEnabledTermsAndConditions();

        $this->modelConfig->saveConfig(
            AgreementsProvider::PATH_ENABLED,
            $isEnabledTAC,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
