<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VerificationSystem implements OptionSourceInterface
{
    const VIES = 0;
    const ISVAT = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VIES, 'label' => __('VIES')],
            ['value' => self::ISVAT, 'label' => __('Isvat')],
        ];
    }
}
