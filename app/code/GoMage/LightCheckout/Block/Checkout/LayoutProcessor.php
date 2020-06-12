<?php

namespace GoMage\LightCheckout\Block\Checkout;

use GoMage\LightCheckout\Model\Block\LayoutProcessor\UpdateBlocksAccordingToConfigurationByJsLayout;
use GoMage\LightCheckout\Model\Block\LayoutProcessor\PrepareAddressFieldsPositions;
use GoMage\LightCheckout\Model\InitGeoIpSettingsForCheckout;
use GoMage\LightCheckout\Model\Layout\FetchArgs;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Shipping\Model\Config;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Ui\Component\Form\AttributeMapper;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    private $attributeMapper;

    /**
     * @var AttributeMerger
     */
    private $merger;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @var \GoMage\LightCheckout\Model\Layout\FetchArgs
     */
    private $fetchArgs;

    /**
     * @var UpdateBlocksAccordingToConfigurationByJsLayout
     */
    private $updateBlocksAccordingToConfigurationByJsLayout;

    /**
     * @var InitGeoIpSettingsForCheckout
     */
    private $initGeoIpSettingsForCheckout;

    /**
     * @var PrepareAddressFieldsPositions
     */
    private $prepareAddressFieldsPositions;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     * @param FetchArgs $fetchArgs
     * @param Config $shippingConfig
     * @param StoreResolverInterface $storeResolver
     * @param Options $options
     * @param UpdateBlocksAccordingToConfigurationByJsLayout $updateBlocksAccordingToConfigurationByJsLayout
     * @param InitGeoIpSettingsForCheckout $initGeoIpSettingsForCheckout
     * @param PrepareAddressFieldsPositions $prepareAddressFieldsPositions
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        FetchArgs $fetchArgs,
        Config $shippingConfig,
        StoreResolverInterface $storeResolver,
        Options $options,
        UpdateBlocksAccordingToConfigurationByJsLayout $updateBlocksAccordingToConfigurationByJsLayout,
        InitGeoIpSettingsForCheckout $initGeoIpSettingsForCheckout,
        PrepareAddressFieldsPositions $prepareAddressFieldsPositions
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->fetchArgs = $fetchArgs;
        $this->shippingConfig = $shippingConfig;
        $this->storeResolver = $storeResolver;
        $this->options = $options;
        $this->updateBlocksAccordingToConfigurationByJsLayout = $updateBlocksAccordingToConfigurationByJsLayout;
        $this->initGeoIpSettingsForCheckout = $initGeoIpSettingsForCheckout;
        $this->prepareAddressFieldsPositions = $prepareAddressFieldsPositions;
    }

    /**
     * @return array
     */
    private function getAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }

        return $elements;
    }

    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements address attributes
     * @param array $attributesToConvert fields and their callbacks
     *
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $key => $value) {
                $elements[$code]['options'][] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }

    /**
     * Process block js Layout.
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $attributesToConvert = [
            'prefix' => [$this->options, 'getNamePrefixOptions'],
            'suffix' => [$this->options, 'getNameSuffixOptions'],
        ];

        $elements = $this->getAddressAttributes();
        $elements = $this->convertElementsToSelect($elements, $attributesToConvert);

        if (isset($jsLayout['components']['checkout']['children']['configuration']['children']
            ['shipping-rates-validation']['children'])) {
            $jsLayout['components']['checkout']['children']['configuration']['children']
            ['shipping-rates-validation']['children'] =
                $this->processShippingRates(
                    $jsLayout['components']['checkout']['children']['configuration']['children']
                    ['shipping-rates-validation']['children']
                );
        }

        if (isset($jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'])) {
            $fields = $jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress',
                $fields
            );
        }

        if (isset($jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children'])) {
            $fields = $jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['billingAddress']['children']
            ['billing-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'billingAddress',
                $fields
            );
        }

        $jsLayout['components']['checkout']['children']['payment']['children']['renders']['children']
            = $this->mergePaymentMethodsRenders(
                $jsLayout['components']['checkout']['children']['payment']['children']['renders']['children']
            );

        $jsLayout['components']['checkout']['children']['payment']['children']['afterMethods']['children']
            = $this->mergePaymentAfterMethods(
                $jsLayout['components']['checkout']['children']['payment']['children']['afterMethods']['children']
            );

        $jsLayout['components']['checkout']['children']['payment']['children']['payments-list']['children']
            = $this->mergeBeforePlaceOrder(
                $jsLayout['components']['checkout']['children']['payment']['children']['payments-list']
                ['children']['before-place-order']
            );

        $jsLayout['components']['checkout']['children']['payment']['children']['additional-payment-validators']
        ['children'] = $this->mergeAdditionalValidators(
            $jsLayout['components']['checkout']['children']['payment']['children']['afterMethods']['children']
        );

        $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']
        ['children'] = $this->mergeSummaryTotals(
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
                ['totals']['children']
        );

        if (isset($jsLayout['components']['checkout']['children']['deliveryDate']['children']
            ['selectTime'])) {
            $jsLayout['components']['checkout']['children']['deliveryDate']['children']
            ['selectTime']['options'] = [];
        }

        $jsLayout = $this->updateTemplateForVatIdField($jsLayout);
        $jsLayout = $this->updateBlocksAccordingToConfigurationByJsLayout->execute($jsLayout);
        $jsLayout = $this->initGeoIpSettingsForCheckout->execute($jsLayout);

        $jsLayout = $this->prepareAddressFieldsPositions->execute($jsLayout);
        $jsLayout = $this->removeTermsAndConditionsFromPaymentMethods($jsLayout);
        $jsLayout = $this->removeDiscountCodeFromPaymentMethods($jsLayout);

        return $jsLayout;
    }

    /**
     * Merge payment method renders from standard path to new path.
     *
     * @param array $renders
     *
     * @return array
     */
    private function mergePaymentMethodsRenders(array $renders)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="renders"]/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $renders);
    }

    /**
     * Process shipping configuration to exclude inactive carriers.
     *
     * @param array $shippingRatesLayout
     *
     * @return array
     */
    private function processShippingRates($shippingRatesLayout)
    {
        $shippingRatesLayout = $this->mergeShippingRatesValidations($shippingRatesLayout);

        $activeCarriers = $this->shippingConfig->getActiveCarriers(
            $this->storeResolver->getCurrentStoreId()
        );

        foreach (array_keys($shippingRatesLayout) as $carrierName) {
            $carrierKey = str_replace('-rates-validation', '', $carrierName);
            if (!array_key_exists($carrierKey, $activeCarriers)) {
                unset($shippingRatesLayout[$carrierName]);
            }
        }

        return $shippingRatesLayout;
    }

    /**
     * Merge shipping rates from standard path to new path.
     *
     * @param $shippingRatesLayout
     *
     * @return array
     */
    private function mergeShippingRatesValidations($shippingRatesLayout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="shipping-step"]'
            . '/item[@name="children"]/item[@name="step-config"]/item[@name="children"]'
            . '/item[@name="shipping-rates-validation"]/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $shippingRatesLayout);
    }

    /**
     * Merge payment after methods from standard path to new path.
     *
     * @param $afterMethodsLayout
     *
     * @return array
     */
    private function mergePaymentAfterMethods($afterMethodsLayout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="afterMethods"]/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $afterMethodsLayout);
    }

    /**
     * @param $layout
     *
     * @return array
     */
    private function mergeBeforePlaceOrder($layout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="payments-list"]/item[@name="children"]/item[@name="before-place-order"]'
            . '/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $layout);
    }

    /**
     * @param $layout
     *
     * @return array
     */
    private function mergeAdditionalValidators($layout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="additional-payment-validators"]/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $layout);
    }

    /**
     * @param $layout
     *
     * @return array
     */
    private function mergeSummaryTotals($layout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="sidebar"]/item[@name="children"]/item[@name="summary"]'
            . '/item[@name="children"]/item[@name="totals"]/item[@name="children"]';

        $args = $this->fetchArgs->execute('checkout_index_index', $path);

        return array_merge($args, $layout);
    }

    /**
     * @param $jsLayout
     *
     * @return array
     */
    private function updateTemplateForVatIdField($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['billingAddress']['children']['billing-address-fieldset']
        ['children']['vat_id']['config']['template'] = 'GoMage_LightCheckout/element/vat-number-with-checkbox';
        $jsLayout['components']['checkout']['children']['billingAddress']['children']['billing-address-fieldset']
        ['children']['vat_id']['config']['elementTmpl'] = 'GoMage_LightCheckout/element/element-with-blur-template';

        $jsLayout['components']['checkout']['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['vat_id']['config']['template'] = 'GoMage_LightCheckout/element/vat-number';
        $jsLayout['components']['checkout']['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['vat_id']['config']['elementTmpl'] = 'GoMage_LightCheckout/element/element-with-blur-template';

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     *
     * @return array
     */
    private function removeTermsAndConditionsFromPaymentMethods($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['payment']['children']['payments-list']
            ['children']['agreements'])
        ) {
            $agreements = $jsLayout['components']['checkout']['children']['payment']['children']['payments-list']
            ['children']['agreements'];
            unset($jsLayout['components']['checkout']['children']['payment']['children']['payments-list']
                ['children']['agreements']);

            $agreements['displayArea'] = 'checkoutAgreements';
            $agreements['component'] = 'GoMage_LightCheckout/js/view/summary/checkout-agreements';
            $jsLayout['components']['checkout']['children']['sidebar']['children']['agreements'] = $agreements;
        }

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     *
     * @return array
     */
    private function removeDiscountCodeFromPaymentMethods($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['payment']['children']['afterMethods']
            ['children']['discount'])
        ) {
            unset($jsLayout['components']['checkout']['children']['payment']['children']['afterMethods']
                ['children']['discount']);
        }

        return $jsLayout;
    }
}
