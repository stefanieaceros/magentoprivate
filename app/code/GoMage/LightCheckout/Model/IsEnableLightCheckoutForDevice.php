<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\OperationSystemsForDevices;

class IsEnableLightCheckoutForDevice
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
     * @return bool
     */
    public function execute()
    {
        if (!class_exists(\Mobile_Detect::class)) {
            return true;
        }

        $detect = new \Mobile_Detect();

        if (!$detect->isMobile()) {
            return (bool)$this->checkoutConfigurationsProvider->isShowOnDesktopAndLaptop();
        }

        if ($detect->isTablet()) {
            $devices = explode(',', $this->checkoutConfigurationsProvider->getShowOnTabletOperationSystems());
        } else {
            $devices = explode(',', $this->checkoutConfigurationsProvider->getShowOnSmartphoneOperationSystems());
        }

        if ($detect->isAndroidOS()) {
            return in_array(OperationSystemsForDevices::ANDROID, $devices);
        }
        if ($detect->isBlackBerryOS()) {
            return in_array(OperationSystemsForDevices::BLACKBERRY, $devices);
        }
        if ($detect->isiOS()) {
            return in_array(OperationSystemsForDevices::IOS, $devices);
        }

        return in_array(OperationSystemsForDevices::OTHER, $devices);
    }
}
