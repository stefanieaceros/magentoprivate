<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Return options array of date formats.
 */
class DateFormats implements OptionSourceInterface
{
    const AMERICAN = 0;
    const EUROPEAN = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::AMERICAN, 'label' => 'MM.DD.YYYY'],
            ['value' => self::EUROPEAN, 'label' => 'DD.MM.YYYY'],
        ];
    }
}
