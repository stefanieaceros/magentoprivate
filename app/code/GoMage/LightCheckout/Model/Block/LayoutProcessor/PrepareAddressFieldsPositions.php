<?php

namespace GoMage\LightCheckout\Model\Block\LayoutProcessor;

use GoMage\LightCheckout\Model\Config\CheckoutAddressFieldsSorting\FieldsProvider;
use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;

class PrepareAddressFieldsPositions
{
    /**
     * @var FieldsProvider
     */
    private $fieldsProvider;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @param FieldsProvider $fieldsProvider
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     */
    public function __construct(
        FieldsProvider $fieldsProvider,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    ) {
        $this->fieldsProvider = $fieldsProvider;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function execute($jsLayout)
    {
        $billingFields = $jsLayout["components"]["checkout"]["children"]["billingAddress"]["children"]
        ["billing-address-fieldset"]["children"];
        $shippingFields = $jsLayout["components"]["checkout"]["children"]["shippingAddress"]["children"]
        ["shipping-address-fieldset"]["children"];

        $preparedBillingFields = $this->prepareByAddressChildren($billingFields);
        $preparedShippingFields = $this->prepareByAddressChildren($shippingFields);

        $jsLayout["components"]["checkout"]["children"]["billingAddress"]["children"]["billing-address-fieldset"]
        ["children"] = $preparedBillingFields;
        $jsLayout["components"]["checkout"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]
        ["children"] = $preparedShippingFields;

        return $jsLayout;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private function prepareByAddressChildren($fields)
    {
        $preparedFields = [];
        $fieldsDataTransferObject = $this->fieldsProvider->get();
        $visibleFields = $fieldsDataTransferObject->getVisibleFields();

        /** @var \Magento\Customer\Model\Attribute $visibleField */
        foreach ($visibleFields as $visibleField) {
            if (isset($fields[$visibleField->getAttributeCode()])) {
                $attributeCode = $visibleField->getAttributeCode();
                $preparedFields[$attributeCode] = $fields[$attributeCode];

                $presentedAddClasses = isset($preparedFields[$attributeCode]['config']['additionalClasses'])
                    ? $preparedFields[$attributeCode]['config']['additionalClasses']
                    : '';

                if (!$visibleField->getIsWide()) {
                    $preparedFields[$attributeCode]['config']['additionalClasses'] = $presentedAddClasses
                        . ' address-half';
                } else {
                    $preparedFields[$attributeCode]['config']['additionalClasses'] = $presentedAddClasses
                        . ' full';
                }

                $presentedAddClasses = isset($preparedFields[$attributeCode]['config']['additionalClasses'])
                    ? $preparedFields[$attributeCode]['config']['additionalClasses']
                    : '';

                if (!$visibleField->getIsNewRow()) {
                    $preparedFields[$attributeCode]['config']['additionalClasses'] = $presentedAddClasses . ' right';
                } else {
                    $preparedFields[$attributeCode]['config']['additionalClasses'] = $presentedAddClasses . ' left';
                }

                $preparedFields[$attributeCode]['sortOrder'] = $visibleField->getSortOrder();

                if ($this->checkoutConfigurationsProvider->getAddressFieldsKeepInside()) {
                    $presentedAddClasses = isset($preparedFields[$attributeCode]['config']['additionalClasses'])
                        ? $preparedFields[$attributeCode]['config']['additionalClasses']
                        : '';
                    if (isset($preparedFields[$attributeCode]['config']['template'])
                        && $preparedFields[$attributeCode]['config']['template'] !== 'ui/group/group'
                    ) {
                        if ($attributeCode === 'region_id') {
                            $preparedFields[$attributeCode]['config']['inputPlaceholder'] =
                                $preparedFields[$attributeCode]['label'];
                        } else {
                            $preparedFields[$attributeCode]['config']['placeholder'] =
                                $preparedFields[$attributeCode]['label'];
                        }

                        $preparedFields[$attributeCode]['label'] = '';
                        $preparedFields[$attributeCode]['config']['additionalClasses'] = $presentedAddClasses . ' inside';
                    }

                    if (isset($preparedFields[$attributeCode]['config']['template'])
                        && $preparedFields[$attributeCode]['config']['template'] === 'ui/group/group'
                    ) {
                        if (isset($preparedFields[$attributeCode]['children'])) {
                            foreach ($preparedFields[$attributeCode]['children'] as $key => $street) {
                                $preparedFields[$attributeCode]['children'][$key]['config']['placeholder'] =
                                    $preparedFields[$attributeCode]['label'];
                                $preparedFields[$attributeCode]['children'][$key]['config']['additionalClasses'] = $presentedAddClasses . ' inside';
                            }
                        }
                        $preparedFields[$attributeCode]['label'] = '';
                    }
                } else {
                    if ($attributeCode === 'region_id') {
                        $preparedFields[$attributeCode]['config']['inputPlaceholder'] = '';
                    }
                }
            }
        }

        return $preparedFields;
    }
}
