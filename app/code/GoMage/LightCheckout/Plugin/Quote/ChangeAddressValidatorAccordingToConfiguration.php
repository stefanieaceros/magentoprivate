<?php

namespace GoMage\LightCheckout\Plugin\Quote;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Directory\Helper\Data;

class ChangeAddressValidatorAccordingToConfiguration
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

    public function aroundValidate(
        \Magento\Quote\Model\Quote\Address $subject,
        \Closure $proceed
    ) {
        $errors = [];
        $isFirstNameRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldFirstName();
        if ($isFirstNameRequired && !\Zend_Validate::is($subject->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }

        $isLastNameRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldLastName();
        if ($isLastNameRequired && !\Zend_Validate::is($subject->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }

        $isStreetRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStreetAddress();
        if ($isStreetRequired && !\Zend_Validate::is($subject->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('Please enter the street.');
        }

        $isCityRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCity();
        if ($isCityRequired && !\Zend_Validate::is($subject->getCity(), 'NotEmpty')) {
            $errors[] = __('Please enter the city.');
        }

        $isPhoneRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldPhoneNumber();
        if ($isPhoneRequired && !\Zend_Validate::is($subject->getTelephone(), 'NotEmpty')) {
            $errors[] = __('Please enter the phone number.');
        }

        $isZipRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldZipPostalCode();
        if ($isZipRequired && !\Zend_Validate::is(
                $subject->getPostcode(),
                'NotEmpty'
            )
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }

        $isCountryRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCountry();
        if ($isCountryRequired && !\Zend_Validate::is($subject->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }

        $isStateRequired = (int)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStateProvince();
        if ($isStateRequired === 2) {
            if ($subject->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is(
                    $subject->getRegionId(),
                    'NotEmpty'
                ) && $this->directoryData->isRegionRequired(
                    $subject->getCountryId()
                )
            ) {
                $errors[] = __('Please enter the state/province.');
            }
        } elseif ($isStateRequired === 1 &&
            !(\Zend_Validate::is($subject->getRegionId(), 'NotEmpty')
            || \Zend_Validate::is($subject->getRegion(), 'NotEmpty'))
        ) {
            $errors[] = __('Please enter the state/province.');
        }

        $isCompanyRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCompany();
        if ($isCompanyRequired && !\Zend_Validate::is($subject->getCompany(), 'NotEmpty')) {
            $errors[] = __('Please enter the company.');
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
