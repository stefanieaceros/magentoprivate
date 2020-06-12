<?php

namespace GoMage\LightCheckout\Model\Config\Source;

use GoMage\LightCheckout\Model\Config\AddressFieldsProvider;
use Magento\Framework\Data\OptionSourceInterface;

class CheckoutFields implements OptionSourceInterface
{
    /**#@+
     * Checkout blocks where help message can be shown.
     */
    const SHIPPING_METHODS = 1;
    const DELIVERY_DATE = 2;
    const PAYMENT_METHOD = 3;
    const ORDER_SUMMARY = 4;
    /**#@-*/

    /**
     * @var AddressFieldsProvider
     */
    private $addressFieldsProvider;

    /**
     * @param AddressFieldsProvider $addressFieldsProvider
     */
    public function __construct(
        AddressFieldsProvider $addressFieldsProvider
    ) {
        $this->addressFieldsProvider = $addressFieldsProvider;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return array_merge(
            $this->getAddressFieldsToOptionArray(),
            [
                ['value' => self::SHIPPING_METHODS, 'label' => __('Shipping Methods')],
                ['value' => self::DELIVERY_DATE, 'label' => __('Delivery Date')],
                ['value' => self::PAYMENT_METHOD, 'label' => __('Payment Method')],
                ['value' => self::ORDER_SUMMARY, 'label' => __('Order Summary')],
            ]
        );
    }

    /**
     * @return array
     */
    private function getAddressFieldsToOptionArray()
    {
        $options = [];
        $addressFields = $this->addressFieldsProvider->get();

        foreach ($addressFields as $addressField) {
            $options[] = [
                'value' => $addressField->getAttributeCode(),
                'label' => $addressField->getFrontendLabel(),
            ];
        }

        return ['address_fields' => ['label' => 'Address Fields', 'value' => $options]];
    }
}
