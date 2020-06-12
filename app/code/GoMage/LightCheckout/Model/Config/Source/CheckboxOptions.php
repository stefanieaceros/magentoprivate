<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckboxOptions implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Unchecked')],
            ['value' => 1, 'label' => __('Checked')],
        ];
    }
}
