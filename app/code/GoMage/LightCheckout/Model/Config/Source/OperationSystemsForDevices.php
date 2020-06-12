<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OperationSystemsForDevices implements OptionSourceInterface
{
    /**#@+
     * Devices OS.
     */
    const ANDROID = 1;
    const BLACKBERRY = 2;
    const IOS = 3;
    const OTHER = 4;
    /**#@-*/
    
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ANDROID, 'label' => __('Android')],
            ['value' => self::BLACKBERRY, 'label' => __('BlackBerry')],
            ['value' => self::IOS, 'label' => __('iOS')],
            ['value' => self::OTHER, 'label' => __('Other')],
        ];
    }
}
