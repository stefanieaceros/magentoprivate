<?php

namespace GoMage\LightCheckout\Plugin\Customer\Model\Address;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Directory\Helper\Data;

class ChangeAddressValidationAccordingToConfiguration
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var Data
     */
    private $directoryData;

    /**
     * ChangeAddressValidationAccordingToConfiguration constructor.
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param Data $directoryData
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        Data $directoryData
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->directoryData = $directoryData;
    }

    /**
     * @param \Magento\Customer\Model\Address\CompositeValidator $subject
     * @param $result
     * @param AbstractAddress $address
     * @return array
     * @throws \Zend_Validate_Exception
     */
    public function afterValidate(
        \Magento\Customer\Model\Address\CompositeValidator $subject,
        $result,
        AbstractAddress $address
    ) {
        $errors = [];

        $isPhoneRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldPhoneNumber();
        if ($isPhoneRequired && !\Zend_Validate::is($address->getTelephone(), 'NotEmpty')) {
            $errors[] = __('Please enter the phone number.');
        }

        $isZipRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldZipPostalCode();
        if ($isZipRequired && !\Zend_Validate::is(
            $address->getPostcode(),
            'NotEmpty'
        )
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }

        $isCountryRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCountry();
        if ($isCountryRequired && !\Zend_Validate::is($address->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }

        $isStateRequired = (int)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStateProvince();
        if ($isStateRequired === 2) {
            if ($address->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
                $address->getRegionId(),
                'NotEmpty'
            ) && $this->directoryData->isRegionRequired(
                $address->getCountryId()
            )
            ) {
                $errors[] = __('Please enter the state/province.');
            }
        } elseif ($isStateRequired === 1 &&
            !(\Zend_Validate::is($address->getRegionId(), 'NotEmpty')
            || \Zend_Validate::is($address->getRegion(), 'NotEmpty'))
        ) {
            $errors[] = __('Please enter the state/province.');
        }

        $isCompanyRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCompany();
        if ($isCompanyRequired && !\Zend_Validate::is($address->getCompany(), 'NotEmpty')) {
            $errors[] = __('Please enter the company.');
        }

        return $errors;
    }
}
