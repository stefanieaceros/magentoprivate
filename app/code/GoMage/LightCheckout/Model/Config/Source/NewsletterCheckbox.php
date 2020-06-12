<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class NewsletterCheckbox implements OptionSourceInterface
{
    const NEWSLETTER_CHECKBOX_DISABLE = 0;
    const NEWSLETTER_CHECKBOX_ENABLE_IN_CHECKOUT = 1;
    const NEWSLETTER_CHECKBOX_ENABLE_ON_SUCCESS_PAGE = 2;
    const NEWSLETTER_CHECKBOX_ENABLE_BOTH = 3;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NEWSLETTER_CHECKBOX_DISABLE, 'label' => __('Disable')],
            ['value' => self::NEWSLETTER_CHECKBOX_ENABLE_IN_CHECKOUT, 'label' => __('Enable in Checkout')],
            ['value' => self::NEWSLETTER_CHECKBOX_ENABLE_ON_SUCCESS_PAGE, 'label' => __('Enable on Success Page')],
            ['value' => self::NEWSLETTER_CHECKBOX_ENABLE_BOTH, 'label' => __('Enable Both')],
        ];
    }
}
