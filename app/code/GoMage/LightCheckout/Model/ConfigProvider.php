<?php

namespace GoMage\LightCheckout\Model;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\ConfigProvider\DeliveryDateConfigProvider;
use GoMage\LightCheckout\Model\ConfigProvider\PasswordSettingProvider;
use GoMage\LightCheckout\Model\ConfigProvider\PaymentMethodsListProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Url;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Quote\Model\Quote\TotalsCollector;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PaymentMethodsListProvider
     */
    private $paymentMethodsListProvider;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var PasswordSettingProvider
     */
    private $passwordSettingProvider;

    /**
     * @var DeliveryDateConfigProvider
     */
    private $deliveryDateConfigProvider;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param PaymentMethodsListProvider $paymentMethodsListProvider
     * @param CheckoutSession $session
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param DirectoryHelper $directoryHelper
     * @param TotalsCollector $totalsCollector
     * @param PasswordSettingProvider $passwordSettingProvider
     * @param DeliveryDateConfigProvider $deliveryDateConfigProvider
     * @param Url $url
     */
    public function __construct(
        PaymentMethodsListProvider $paymentMethodsListProvider,
        CheckoutSession $session,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        PaymentMethodManagementInterface $paymentMethodManagement,
        ShippingMethodConverter $shippingMethodConverter,
        DirectoryHelper $directoryHelper,
        TotalsCollector $totalsCollector,
        PasswordSettingProvider $passwordSettingProvider,
        DeliveryDateConfigProvider $deliveryDateConfigProvider,
        Url $url
    ) {
        $this->paymentMethodsListProvider = $paymentMethodsListProvider;
        $this->checkoutSession = $session;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->shippingMethodConverter = $shippingMethodConverter;
        $this->directoryHelper = $directoryHelper;
        $this->totalsCollector = $totalsCollector;
        $this->passwordSettingProvider = $passwordSettingProvider;
        $this->deliveryDateConfigProvider = $deliveryDateConfigProvider;
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $config = [
            'paymentMethods' => $this->paymentMethodsListProvider->get($quoteId),
            'general' => $this->getGeneralConfig(),
            'passwordSettings' => $this->passwordSettingProvider->get(),
            'registration' => $this->getRegistrationConfig(),
            'deliveryDate' => $this->deliveryDateConfigProvider->get(),
            'vatTax' => $this->getVatTaxConfig(),
            'autoCompleteStreet' => $this->getAutoCompleteStreetConfig(),
            'addressFields' => $this->getAddressFieldsConfig(),
            'mandatorySettings' => $this->getMandatorySettings(),
            'numberProductInCheckout' => $this->getProductNumberInCheckoutSettings(),
        ];

        return $config;
    }

    /**
     * @param CartInterface $quote
     *
     * @return string|null
     */
    private function getDefaultPaymentMethod(CartInterface $quote)
    {
        $defaultActivePaymentMethod = null;

        if (!$quote->getPayment()->getMethod()) {
            $defaultPaymentMethod = $this->checkoutConfigurationsProvider->getDefaultPaymentMethod();

            $paymentMethods = $this->paymentMethodManagement->getList($quote->getId());

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->getCode() === $defaultPaymentMethod) {
                    $defaultActivePaymentMethod = $defaultPaymentMethod;
                    break;
                }
            }
        }

        return $defaultActivePaymentMethod;
    }

    /**
     * @param CartInterface $quote
     *
     * @return string|null
     */
    private function getDefaultShippingMethod(CartInterface $quote)
    {
        $defaultActiveShippingMethod = null;

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getCountryId()) {
            $defaultCountryId = $this->directoryHelper->getDefaultCountry();
            $shippingAddress->setCountryId($defaultCountryId)->setCollectShippingRates(true);
        }

        if (!$shippingAddress->getShippingMethod() && !$quote->getIsVirtual()) {
            $defaultShippingMethod = $this->checkoutConfigurationsProvider->getDefaultShippingMethod();

            if ($defaultShippingMethod) {
                $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);

                $allowedShippingMethods = $this->getAllowedShippingMethodsByQuote($quote);

                if (in_array($defaultShippingMethod, $allowedShippingMethods)) {
                    $defaultActiveShippingMethod = $defaultShippingMethod;
                }
            }
        }

        return $defaultActiveShippingMethod;
    }

    /**
     * @param CartInterface $quote
     *
     * @return array
     */
    private function getAllowedShippingMethodsByQuote(CartInterface $quote)
    {
        $allowedShippingMethods = [];
        $currencyCode = $quote->getQuoteCurrencyCode();

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $shippingMethod = $this->shippingMethodConverter->modelToDataObject($rate, $currencyCode);
                $allowedShippingMethods[] = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
            }
        }

        return $allowedShippingMethods;
    }

    /**
     * @return array
     */
    private function getGeneralConfig()
    {
        return [
            'pageContent' => $this->checkoutConfigurationsProvider->getPageContent(),
            'enableDifferentShippingAddress' =>
                $this->checkoutConfigurationsProvider->getEnableDifferentShippingAddress(),
            'defaultPaymentMethod' => $this->getDefaultPaymentMethod($this->checkoutSession->getQuote()),
            'defaultShippingMethod' => $this->getDefaultShippingMethod($this->checkoutSession->getQuote()),
        ];
    }

    /**
     * @return array
     */
    private function getRegistrationConfig()
    {
        return [
            'checkoutMode' => $this->checkoutConfigurationsProvider->getCheckoutMode(),
            'isCreateAnAccountCheckboxChecked' => $this->checkoutConfigurationsProvider->getCreateAnAccountCheckbox(),
            'autoRegistration' => $this->checkoutConfigurationsProvider->getIsAutoRegistration(),
            'registrationUrl' => $this->url->getRegisterUrl(),
        ];
    }

    /**
     * @return array
     */
    private function getVatTaxConfig()
    {
        return [
            'enabled' => $this->checkoutConfigurationsProvider->getIsEnabledVatTax(),
            'checkboxSettings' => $this->checkoutConfigurationsProvider->getVatTaxShowCheckbox(),
            'checkboxText' => $this->checkoutConfigurationsProvider->getVatTaxTextUnderTaxVatField(),
        ];
    }

    /**
     * @return array
     */
    private function getAutoCompleteStreetConfig()
    {
        return [
            'enabled' => $this->checkoutConfigurationsProvider->getIsEnabledAutoCompleteByStreet(),
        ];
    }

    /**
     * @return array
     */
    private function getAddressFieldsConfig()
    {
        return [
            'keepInside' => $this->checkoutConfigurationsProvider->getAddressFieldsKeepInside(),
        ];
    }

    /**
     * @return array
     */
    private function getMandatorySettings()
    {
        return [
          'isMandatory' => $this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStateProvince()
        ];
    }

    /**
     * @return array
     */
    private function getProductNumberInCheckoutSettings()
    {
        return [
            'hideProducts' => (bool)$this->checkoutConfigurationsProvider->getIsHidedNumberOfProducts(),
            'numberOfProducts' => $this->checkoutConfigurationsProvider->getNumberOfProductsVisibleInCheckout(),
        ];
    }
}
