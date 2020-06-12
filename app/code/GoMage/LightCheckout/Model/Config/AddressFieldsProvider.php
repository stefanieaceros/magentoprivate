<?php

namespace GoMage\LightCheckout\Model\Config;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Eav\Api\Data\AttributeInterface;

class AddressFieldsProvider
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var Address
     */
    private $address;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param Address $address
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        Address $address
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->address = $address;
    }

    /**
     * @return array
     */
    public function get()
    {
        $addressFields = [];

        /** @var AttributeInterface[] $collection */
        $collection = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );
        foreach ($collection as $key => $field) {
            if (!$this->isAddressAttributeVisible($field)) {
                continue;
            }
            $addressFields[] = $field;
        }

        /** @var AttributeInterface[] $collection */
        $collection = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_account_create'
        );
        foreach ($collection as $key => $field) {
            if (!$this->isCustomerAttributeVisible($field)) {
                continue;
            }
            $addressFields[] = $field;
        }

        return $addressFields;
    }

    /**
     * Check if address attribute can be visible on frontend.
     *
     * @param $attribute
     *
     * @return bool|null|string
     */
    private function isAddressAttributeVisible($attribute)
    {
        $code = $attribute->getAttributeCode();
        $result = $attribute->getIsVisible();
        switch ($code) {
            case 'vat_id':
                $result = $this->address->isVatAttributeVisible();
                break;
            case 'region':
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    private function isCustomerAttributeVisible(AttributeInterface $attribute)
    {
        $code = $attribute->getAttributeCode();
        if (in_array($code, ['gender', 'taxvat', 'dob'])) {
            return $attribute->getIsVisible();
        } else {
            if (!$attribute->getIsUserDefined()) {
                return false;
            }
        }

        return true;
    }
}
