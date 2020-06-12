<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AddressFieldStateOptions implements OptionSourceInterface
{
    const NO_REQUIRED = 0;
    const REQUIRED = 1;
    const USE_MAGENTO_SETTINGS = 2;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NO_REQUIRED, 'label' => __('No')],
            ['value' => self::REQUIRED, 'label' => __('Yes')],
            ['value' => self::USE_MAGENTO_SETTINGS, 'label' => __('Use Magento Settings')],
        ];
    }
}
