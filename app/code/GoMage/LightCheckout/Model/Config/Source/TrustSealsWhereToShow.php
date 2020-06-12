<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TrustSealsWhereToShow implements OptionSourceInterface
{
    /**#@+
     * Where to show trust seals.
     */
    const TOP_OF_THE_PAGE = 1;
    const ABOVE_PLACE_ORDER_BUTTON = 2;
    const UNDER_PLACE_ORDER_BUTTON = 3;
    /**#@-*/

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return
            [
                ['value' => self::TOP_OF_THE_PAGE, 'label' => __('On the Top of the Page')],
                ['value' => self::ABOVE_PLACE_ORDER_BUTTON, 'label' => __('Above Place Order Button')],
                ['value' => self::UNDER_PLACE_ORDER_BUTTON, 'label' => __('Under Place Order Button')],
            ];
    }
}
