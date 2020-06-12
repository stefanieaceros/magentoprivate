<?php

namespace GoMage\LightCheckout\Plugin\Checkout\Controller\Index;

use GoMage\Core\Helper\Data;
use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\IsEnableLightCheckoutForDevice;
use GoMage\LightCheckout\Setup\InstallData;
use Magento\Checkout\Controller\Index\Index as CheckoutIndex;
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
     * @var IsEnableLightCheckoutForDevice
     */
    private $isEnableLightCheckoutForDevice;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param ResultFactory $resultFactory
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param IsEnableLightCheckoutForDevice $isEnableLightCheckoutForDevice
     * @param Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        IsEnableLightCheckoutForDevice $isEnableLightCheckoutForDevice,
        Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->isEnableLightCheckoutForDevice = $isEnableLightCheckoutForDevice;
        $this->helper = $helper;
    }

    /**
     * Forward to Light Checkout if it is needed.
     *
     * @param CheckoutIndex $subject
     * @param \Closure $proceed
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(CheckoutIndex $subject, \Closure $proceed)
    {
        if ($this->helper->isA(InstallData::MODULE_NAME)
            && $this->checkoutConfigurationsProvider->isLightCheckoutEnabled()
            && $this->isEnableLightCheckoutForDevice->execute()
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
