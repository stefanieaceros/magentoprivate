<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CountryMode implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('With VAT')],
            ['value' => 1, 'label' => __('Without VAT if VAT Number is Verified')],
            ['value' => 2, 'label' => __('Without VAT')],
        ];
    }
}
