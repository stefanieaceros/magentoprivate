<?php

namespace GoMage\LightCheckout\Plugin\Customer\Model\Attribute\Data;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use Magento\Customer\Model\Attribute\Data\Postcode;

/**
 * Class ValidatePostcode
 * @package GoMage\LightCheckout\Plugin\Customer\Model\Attribute\Data
 */
class ValidatePostcode
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * ValidatePostcode constructor.
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
    }

    /**
     * @param Postcode $subject
     * @param $result
     * @param $value
     * @return array|bool
     * @throws \Zend_Validate_Exception
     */
    public function afterValidateValue(
        Postcode $subject,
        $result,
        $value
    ) {
        $errors = [];

        $isZipRequired = (bool) $this->checkoutConfigurationsProvider->getIsRequiredAddressFieldZipPostalCode();
        if ($isZipRequired && !\Zend_Validate::is($value, 'NotEmpty')
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
