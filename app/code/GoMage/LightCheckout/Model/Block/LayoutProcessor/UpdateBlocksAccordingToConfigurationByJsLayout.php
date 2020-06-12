<?php

namespace GoMage\LightCheckout\Model\Block\LayoutProcessor;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\Config\Source\AddressFieldStateOptions;
use GoMage\LightCheckout\Model\Config\Source\CheckoutFields;
use GoMage\LightCheckout\Model\Config\Source\NewsletterCheckbox;
use GoMage\LightCheckout\Model\Config\Source\TrustSealsWhereToShow;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Unset blocks according to configuration.
 */
class UpdateBlocksAccordingToConfigurationByJsLayout
{
    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param UrlInterface $urlBuilder
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        UrlInterface $urlBuilder,
        Session $customerSession,
        SubscriberFactory $subscriberFactory
    ) {
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function execute($jsLayout)
    {
        $jsLayout = $this->disableDiscountCodesAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->disableDeletingItemOnCheckoutAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->disableChangingQtyOnCheckoutAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->disableDeliveryDateOnCheckoutAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->disableAddressFieldsAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->disableCommentOrderBlockOnCheckoutAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->updateTemplateForPostcodeFieldAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->addHelpMessagesAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->addTrustSealsAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->addSocialNetworksAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->updateSubscribeToNewsletterAccordingToTheConfiguration($jsLayout);
        $jsLayout = $this->updateRequiredFields($jsLayout);

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function disableDiscountCodesAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledDiscountCodes = $this->checkoutConfigurationsProvider->getIsEnabledDiscountCodes();

        if (!$isEnabledDiscountCodes) {
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['discount']);
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function disableDeletingItemOnCheckoutAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledRemoveItemFromCheckout = $this->checkoutConfigurationsProvider
            ->getIsAllowedToRemoveItemFromCheckout();

        if (!$isEnabledRemoveItemFromCheckout) {
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['cart_items']['children']['details']['children']['delete_item']);
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function disableChangingQtyOnCheckoutAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledChangeQty = $this->checkoutConfigurationsProvider->getIsAllowedToChangeQty();

        if (!$isEnabledChangeQty) {
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['cart_items']['children']['details']['children']['increase_item_qty']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                ['children']['cart_items']['children']['details']['children']['decrease_item_qty']);
        } else {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
            ['cart_items']['config']['productClasses'] = 'product-item allow-to-change-qty';
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function disableDeliveryDateOnCheckoutAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledDeliveryDate = $this->checkoutConfigurationsProvider->getIsEnabledDeliveryDate();

        if (!$isEnabledDeliveryDate) {
            unset($jsLayout['components']['checkout']['children']['deliveryDate']);
        } else {
            $isShowTime = $this->checkoutConfigurationsProvider->getIsShowTime();
            if (!$isShowTime) {
                unset($jsLayout['components']['checkout']['children']['deliveryDate']['children']['selectTime']);
            }
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function disableAddressFieldsAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledAutofill = $this->checkoutConfigurationsProvider->getIsEnabledAutoFillByZipCode();
        $idDisableAddressFields = $this->checkoutConfigurationsProvider->getAutoFillByZipCodeIsDisabledAddressFields();

        if ($isEnabledAutofill && $idDisableAddressFields) {
            $jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children']['region_id']['disabled'] = true;
            $jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children']['city']['disabled'] = true;
            $jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children']['country_id']['disabled'] = true;

            $jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children']['region_id']['disabled'] = true;
            $jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children']['city']['disabled'] = true;
            $jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children']['country_id']['disabled'] = true;
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function updateTemplateForPostcodeFieldAccordingToTheConfiguration($jsLayout)
    {
        if ($this->checkoutConfigurationsProvider->getIsEnabledAutoFillByZipCode()) {
            $jsLayout['components']['checkout']['children']['billingAddress']['children']['billing-address-fieldset']
            ['children']['postcode']['config']['elementTmpl']
                = 'GoMage_LightCheckout/element/element-with-blur-template';
            $jsLayout['components']['checkout']['children']['billingAddress']['children']['billing-address-fieldset']
            ['children']['postcode']['component'] = 'GoMage_LightCheckout/js/view/post-code';

            $jsLayout['components']['checkout']['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['postcode']['config']['elementTmpl']
                = 'GoMage_LightCheckout/element/element-with-blur-template';
            $jsLayout['components']['checkout']['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['postcode']['component'] = 'GoMage_LightCheckout/js/view/post-code';
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function addHelpMessagesAccordingToTheConfiguration($jsLayout)
    {
        $helpMessages = $this->checkoutConfigurationsProvider->getHelpMessages();

        if ($helpMessages) {
            $helpMessages = json_decode($helpMessages, true);

            foreach ($helpMessages as $helpMessage) {
                if (!is_numeric($helpMessage['field'])) {
                    $jsLayout = $this->addToolTipMessageForFieldByAddressType(
                        $jsLayout,
                        'billing',
                        $helpMessage['field'],
                        $helpMessage['help_message']
                    );
                    $jsLayout = $this->addToolTipMessageForFieldByAddressType(
                        $jsLayout,
                        'shipping',
                        $helpMessage['field'],
                        $helpMessage['help_message']
                    );
                } else {
                    switch ($helpMessage['field']) {
                        case CheckoutFields::SHIPPING_METHODS:
                            $jsLayout['components']['checkout']['children']['shippingAddress']
                            ['tooltip']['description'] = $helpMessage['help_message'];
                            break;
                        case CheckoutFields::DELIVERY_DATE:
                            $jsLayout['components']['checkout']['children']['deliveryDate']
                            ['tooltip']['description'] = $helpMessage['help_message'];
                            break;
                        case CheckoutFields::PAYMENT_METHOD:
                            $jsLayout['components']['checkout']['children']['payment']['children']['payments-list']
                            ['tooltip']['description'] = $helpMessage['help_message'];
                            break;
                        case CheckoutFields::ORDER_SUMMARY:
                            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                            ['tooltip']['description'] = $helpMessage['help_message'];
                            break;
                    }
                }
            }
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @param string $addressType
     * @param string $field
     * @param string $message
     *
     * @return array
     */
    private function addToolTipMessageForFieldByAddressType($jsLayout, $addressType, $field, $message)
    {
        $jsLayout['components']['checkout']['children'][$addressType . 'Address']['children']
        [$addressType . '-address-fieldset']['children'][$field]['config']['tooltip']['description'] = $message;

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function addTrustSealsAccordingToTheConfiguration($jsLayout)
    {
        if ($this->checkoutConfigurationsProvider->getIsEnabledTrustSeals()) {
            $trustSeals = json_decode($this->checkoutConfigurationsProvider->getTrustSealsSeals(), true);

            $trustSealTop = '';
            $trustSealBeforePlaceOrderButton = '';
            $trustSealAfterPlaceOrderButton = '';
            foreach ($trustSeals as $trustSeal) {
                $whereToShow = (int)$trustSeal['where_to_show'];
                if ($whereToShow === TrustSealsWhereToShow::TOP_OF_THE_PAGE) {
                    $trustSealTop .= $trustSeal['trust_seal'];
                } elseif ($whereToShow === TrustSealsWhereToShow::ABOVE_PLACE_ORDER_BUTTON) {
                    $trustSealBeforePlaceOrderButton .= $trustSeal['trust_seal'];
                } elseif ($whereToShow === TrustSealsWhereToShow::UNDER_PLACE_ORDER_BUTTON) {
                    $trustSealAfterPlaceOrderButton .= $trustSeal['trust_seal'];
                }
            }

            $jsLayout['components']['checkout']['children']['trust_seals_top']['html'] = $trustSealTop;
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['trust_seals_before_place_order_button']['html'] = $trustSealBeforePlaceOrderButton;
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['trust_seals_after_place_order_button']['html'] = $trustSealAfterPlaceOrderButton;
        } else {
            unset($jsLayout['components']['checkout']['children']['trust_seals_top']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']
                ['trust_seals_before_place_order_button']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']
                ['trust_seals_after_place_order_button']);
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function addSocialNetworksAccordingToTheConfiguration($jsLayout)
    {
        if ($this->checkoutConfigurationsProvider->getIsSocialLoginGoogleEnabled()) {
            $jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
            ['children']['google']['urlTo'] = $this->urlBuilder->getUrl(
                'lightcheckout/social/login',
                ['type' => 'Google']
            );
        } else {
            unset($jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
                ['children']['google']);
        }

        if ($this->checkoutConfigurationsProvider->getIsSocialLoginFacebookEnabled()) {
            $jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
            ['children']['facebook']['urlTo'] = $this->urlBuilder->getUrl(
                'lightcheckout/social/login',
                ['type' => 'Facebook']
            );
        } else {
            unset($jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
                ['children']['facebook']);
        }

        if ($this->checkoutConfigurationsProvider->getIsSocialLoginTwitterEnabled()) {
            $jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
            ['children']['twitter']['urlTo'] = $this->urlBuilder->getUrl(
                'lightcheckout/social/login',
                ['type' => 'Twitter']
            );
        } else {
            unset($jsLayout['components']['checkout']['children']['customer-email']['children']['social-networks']
                ['children']['twitter']);
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function updateSubscribeToNewsletterAccordingToTheConfiguration($jsLayout)
    {
        $isEnabled = (int)$this->checkoutConfigurationsProvider->getIsEnabledSubscribeToNewsletter();
        $isCustomerLogin = $this->customerSession->isLoggedIn();
        $customerId = $this->customerSession->getCustomerId();
        $isSubscribed = $this->subscriberFactory->create()->loadByCustomerId($customerId);

        if ($isEnabled === NewsletterCheckbox::NEWSLETTER_CHECKBOX_DISABLE
            || $isEnabled === NewsletterCheckbox::NEWSLETTER_CHECKBOX_ENABLE_ON_SUCCESS_PAGE
            || ($isCustomerLogin && $isSubscribed->getStatus() == Subscriber::STATUS_SUBSCRIBED)
        ) {
            unset($jsLayout['components']['checkout']['children']['customer-email']['children']['subscribeNewsletter']);
        } elseif (
            $isEnabled === NewsletterCheckbox::NEWSLETTER_CHECKBOX_ENABLE_IN_CHECKOUT
            || $isEnabled === NewsletterCheckbox::NEWSLETTER_CHECKBOX_ENABLE_BOTH
        ) {
            $isChecked = (bool)$this->checkoutConfigurationsProvider->getSubscribeToNewsletterIsCheckboxChecked();
            $jsLayout['components']['checkout']['children']['customer-email']['children']
            ['subscribeNewsletter']['config']['checked'] = $isChecked;
        }
        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function updateRequiredFields($jsLayout)
    {
        $isFirstNameRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldFirstName();
        $isLastNameRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldLastName();
        $isStreetRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStreetAddress();
        $isCityRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCity();
        $isPhoneRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldPhoneNumber();
        $isZipRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldZipPostalCode();
        $isCountryRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCountry();
        $mandatoryStateSetting = $this->checkoutConfigurationsProvider->getIsRequiredAddressFieldStateProvince();
        if ($mandatoryStateSetting == AddressFieldStateOptions::NO_REQUIRED) {
            $mandatoryStateSetting = 'no_required';
        } elseif ($mandatoryStateSetting == AddressFieldStateOptions::REQUIRED) {
            $mandatoryStateSetting = 'required';
        } elseif ($mandatoryStateSetting == AddressFieldStateOptions::USE_MAGENTO_SETTINGS) {
            $mandatoryStateSetting = 'use_magento_settings';
        }
        $isCompanyRequired = (bool)$this->checkoutConfigurationsProvider->getIsRequiredAddressFieldCompany();

        $shippingAddressFieldset = $jsLayout['components']['checkout']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'];

        $billingAddressFieldset = $jsLayout['components']['checkout']['children']['billingAddress']['children']
        ['billing-address-fieldset']['children'];

        $shippingAddressFieldset['firstname']['validation']['required-entry'] = $isFirstNameRequired;
        $shippingAddressFieldset['lastname']['validation']['required-entry'] = $isLastNameRequired;
        if (isset($shippingAddressFieldset['street']['children'][0])) {
            $shippingAddressFieldset['street']['children'][0]['validation']['required-entry'] = $isStreetRequired;
        }
        $shippingAddressFieldset['city']['validation']['required-entry'] = $isCityRequired;
        $shippingAddressFieldset['telephone']['validation']['required-entry'] = $isPhoneRequired;
        $shippingAddressFieldset['postcode']['validation']['required-entry'] = $isZipRequired;
        $shippingAddressFieldset['country_id']['validation']['required-entry'] = $isCountryRequired;
        $shippingAddressFieldset['region_id']['mandatorySetting'] = $mandatoryStateSetting;
        $shippingAddressFieldset['company']['validation']['required-entry'] = $isCompanyRequired;

        $shippingAddressFieldset['firstname'] = $this->changeLabelIfRequired($shippingAddressFieldset['firstname']);
        $shippingAddressFieldset['lastname'] = $this->changeLabelIfRequired($shippingAddressFieldset['lastname']);
        $shippingAddressFieldset['street'] = $this->changeLabelIfStreetRequired($shippingAddressFieldset['street']);
        $shippingAddressFieldset['city'] = $this->changeLabelIfRequired($shippingAddressFieldset['city']);
        $shippingAddressFieldset['telephone'] = $this->changeLabelIfRequired($shippingAddressFieldset['telephone']);
        $shippingAddressFieldset['postcode'] = $this->changeLabelIfRequired($shippingAddressFieldset['postcode']);
        $shippingAddressFieldset['country_id'] = $this->changeLabelIfRequired($shippingAddressFieldset['country_id']);
        $shippingAddressFieldset['region_id'] = $this->changeLabelIfRequiredStateField($shippingAddressFieldset['region_id']);
        $shippingAddressFieldset['company'] = $this->changeLabelIfRequired($shippingAddressFieldset['company']);

        if (isset($shippingAddressFieldset['vat_id']) && isset($shippingAddressFieldset['vat_id']['label'])) {
            $shippingAddressFieldset['vat_id']['label'] .= ' (' . __('Optional') . ')';
        }

        $billingAddressFieldset['firstname']['validation']['required-entry'] = $isFirstNameRequired;
        $billingAddressFieldset['lastname']['validation']['required-entry'] = $isLastNameRequired;
        if (isset($billingAddressFieldset['street']['children'][0])) {
            $billingAddressFieldset['street']['children'][0]['validation']['required-entry'] = $isStreetRequired;
        }
        $billingAddressFieldset['city']['validation']['required-entry'] = $isCityRequired;
        $billingAddressFieldset['telephone']['validation']['required-entry'] = $isPhoneRequired;
        $billingAddressFieldset['postcode']['validation']['required-entry'] = $isZipRequired;
        $billingAddressFieldset['country_id']['validation']['required-entry'] = $isCountryRequired;
        $billingAddressFieldset['region_id']['mandatorySetting'] = $mandatoryStateSetting;
        $billingAddressFieldset['company']['validation']['required-entry'] = $isCompanyRequired;

        $billingAddressFieldset['firstname'] = $this->changeLabelIfRequired($billingAddressFieldset['firstname']);
        $billingAddressFieldset['lastname'] = $this->changeLabelIfRequired($billingAddressFieldset['lastname']);
        $billingAddressFieldset['street'] = $this->changeLabelIfStreetRequired($billingAddressFieldset['street']);
        $billingAddressFieldset['city'] = $this->changeLabelIfRequired($billingAddressFieldset['city']);
        $billingAddressFieldset['telephone'] = $this->changeLabelIfRequired($billingAddressFieldset['telephone']);
        $billingAddressFieldset['postcode'] = $this->changeLabelIfRequired($billingAddressFieldset['postcode']);
        $billingAddressFieldset['country_id'] = $this->changeLabelIfRequired($billingAddressFieldset['country_id']);
        $billingAddressFieldset['region_id'] = $this->changeLabelIfRequiredStateField($billingAddressFieldset['region_id']);
        $billingAddressFieldset['company'] = $this->changeLabelIfRequired($billingAddressFieldset['company']);

        if (isset($billingAddressFieldset['vat_id']) && isset($billingAddressFieldset['vat_id']['label'])) {
            $billingAddressFieldset['vat_id']['label'] .= ' (' . __('Optional') . ')';
        }

        $jsLayout['components']['checkout']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'] = $shippingAddressFieldset;

        $jsLayout['components']['checkout']['children']['billingAddress']['children']
        ['billing-address-fieldset']['children'] = $billingAddressFieldset;

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    private function disableCommentOrderBlockOnCheckoutAccordingToTheConfiguration($jsLayout)
    {
        $isEnabledCommentOederBlock = $this->checkoutConfigurationsProvider->getIsShownOrderCommentBlock();

        if (!$isEnabledCommentOederBlock) {
            unset($jsLayout['components']['checkout']['children']['commentOrder']);
        }
        return $jsLayout;
    }

    /**
     * @param $field
     *
     * @return array
     */
    private function changeLabelIfRequired($field)
    {
        $isRequired = $field['validation']['required-entry'];
        if (!$isRequired && array_key_exists('label', $field)) {
            $field['label'] .= ' (' . __('Optional') . ')';
        }
        return $field;
    }

    /**
     * @param $field
     *
     * @return array
     */
    private function changeLabelIfRequiredStateField($field)
    {
        if ($field['mandatorySetting'] == 'no_required' && array_key_exists('label', $field)) {
            $field['label'] .= ' (' . __('Optional') . ')';
        }
        return $field;
    }

    /**
     * @param $field
     *
     * @return array
     */
    private function changeLabelIfStreetRequired($field)
    {
        if (isset($field['children'][0]['validation']['required-entry'])) {
            $isRequired = $field['children'][0]['validation']['required-entry'];
            if (!$isRequired && array_key_exists('label', $field)) {
                $field['label'] .= ' (' . __('Optional') . ')';
            }
        }
        return $field;
    }
}
