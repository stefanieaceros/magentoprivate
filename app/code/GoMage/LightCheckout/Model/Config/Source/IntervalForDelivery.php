<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IntervalForDelivery implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('None')],
            ['value' => 1, 'label' => '1'],
            ['value' => 2, 'label' => '2'],
            ['value' => 3, 'label' => '3'],
            ['value' => 4, 'label' => '4'],
            ['value' => 5, 'label' => '5'],
            ['value' => 6, 'label' => '6'],
            ['value' => 7, 'label' => '7'],
            ['value' => 8, 'label' => '8'],
            ['value' => 9, 'label' => '9'],
            ['value' => 10, 'label' => '10'],
            ['value' => 11, 'label' => '11'],
            ['value' => 12, 'label' => '12'],
            ['value' => 13, 'label' => '13'],
            ['value' => 14, 'label' => '14'],
        ];
    }
}
