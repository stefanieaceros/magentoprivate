define(
    [
        'ko',
        'jquery',
        'Magento_Ui/js/form/form',
        'uiRegistry',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Magento_Checkout/js/model/postcode-validator',
        'mage/translate',
        'underscore',
        'Magento_Customer/js/model/customer',
        'GoMage_LightCheckout/js/model/address/auto-complete-register',
        'rjsResolver',
        'Magento_Checkout/js/action/select-shipping-address',
        'GoMage_LightCheckout/js/light-checkout-data',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/action/create-billing-address'
    ],
    function (
        ko,
        $,
        Component,
        uiRegistry,
        checkoutData,
        quote,
        addressConverter,
        selectBillingAddress,
        additionalValidators,
        shippingRatesValidationRules,
        postcodeValidator,
        $t,
        _,
        customer,
        autoCompleteRegister,
        rjsResolver,
        selectShippingAddress,
        lightCheckoutData,
        globalMessageList,
        setBillingAddressAction,
        createShippingAddress,
        addressList,
        createBillingAddress
    ) {
        'use strict';

        var observedElements = [],
            postcodeElement = null,
            postcodeElementName = 'postcode',
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        return Component.extend({
            defaults: {
                imports: {
                    isPlaceOrderButtonClicked: 'checkout.sidebar.placeOrderButton:isPlaceOrderButtonClicked'
                }
            },
            addressOptions: addressOptions,

            initialize: function () {
                var self = this;
                this._super();

                uiRegistry.async('checkoutProvider')(function (checkoutProvider) {
                    var billingAddressData = checkoutData.getBillingAddressFromData();

                    if (billingAddressData) {
                        checkoutProvider.set(
                            'billingAddress',
                            $.extend(true, {}, checkoutProvider.get('billingAddress'), billingAddressData)
                        );
                    }
                    checkoutProvider.on('billingAddress', function (billingAddressData) {
                        checkoutData.setBillingAddressFromData(billingAddressData);
                    });
                });

                this.initFields();


                quote.billingAddress.subscribe(function (newAddress) {
                    if (self.isAddressSameAsShipping() && typeof self.isPlaceOrderButtonClicked !== 'undefined'
                        && !self.isPlaceOrderButtonClicked)
                    {
                        selectShippingAddress(newAddress);
                    }
                });

                additionalValidators.registerValidator(this);

                rjsResolver(this.registerAutoComplete.bind(this));

                return this;
            },

            registerAutoComplete: function () {
                autoCompleteRegister.register('billing');
            },

            initObservable: function () {
                this._super()
                    .observe({
                        isAddressSameAsShipping: false,
                        isNewAddressLinkVisible: customer.isLoggedIn(),
                        saveInAddressBook: 1
                    });

                if (this.addressOptions.length > 0) {
                    for (var i = 0; i < this.addressOptions.length; i++) {
                        if (this.addressOptions[i].isDefaultBilling()) {
                            this.onAddressChange(this.addressOptions[i]);
                            break;
                        }
                    }
                }

                var enableDifferentShippingAddress = parseInt(window.checkoutConfig.general.enableDifferentShippingAddress);

                // set isAddressSameAsShipping for model lightCheckoutData
                if (enableDifferentShippingAddress === 0 || enableDifferentShippingAddress === 1) {
                    if (null === lightCheckoutData.getIsAddressSameAsShipping()) {
                        lightCheckoutData.setIsAddressSameAsShipping(true);
                    }
                } else if (enableDifferentShippingAddress === 2) {
                    if (null === lightCheckoutData.getIsAddressSameAsShipping()) {
                        lightCheckoutData.setIsAddressSameAsShipping(false);
                    }
                }

                // get if saved after page refreshed.
                var isAddressSameAsShipping = lightCheckoutData.getIsAddressSameAsShipping();

                // isAddressSameAsShipping property for UI
                if (isAddressSameAsShipping !== null) {
                    this.isAddressSameAsShipping(isAddressSameAsShipping)
                }

                // update lightCheckoutData model's isAddressSameAsShipping property
                this.isAddressSameAsShipping.subscribe(function (newValue) {
                    lightCheckoutData.setIsAddressSameAsShipping(newValue);
                });

                return this;
            },

            validate: function () {
                var result, isCustomerHasAddresses = true;

                if (customer.isLoggedIn()) { // if customer is not logged in customer.customerData.addresses doesn't exist
                    if (typeof customer.customerData.addresses.length !== 'undefined' &&
                        customer.customerData.addresses.length === 0) {
                        isCustomerHasAddresses = false;
                    }
                } else {
                    isCustomerHasAddresses = false;
                }

                if (!this.isNewAddressLinkVisible() || !isCustomerHasAddresses) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('billingAddress.data.validate');

                    if (this.source.get('billingAddress.custom_attributes')) {
                        this.source.trigger('billingAddress.custom_attributes.data.validate');
                    }

                    result = !this.source.get('params.invalid');
                    if (result) {
                        this.saveBillingAddress();
                    }
                }

                return result;
            },

            saveBillingAddress: function() {
                var addressFlat = uiRegistry.get('checkoutProvider').billingAddress;
                var addressData = addressConverter.formAddressDataToQuoteAddress(addressFlat);

                if (customer.isLoggedIn() && !this.customerHasAddresses) {
                    this.saveInAddressBook(1);
                }

                addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                addressData.saveInAddressBook = this.saveInAddressBook() ? 1 : 0;
                selectBillingAddress(addressData);
            },

            /**
             * Perform postponed binding for fieldset elements
             */
            initFields: function () {
                var formPath = 'checkout.billingAddress.billing-address-fieldset',
                    self = this,
                    elements = shippingRatesValidationRules.getObservableFields();

                if ($.inArray(postcodeElementName, elements) === -1) {
                    // Add postcode field to observables if not exist for zip code validation support
                    elements.push(postcodeElementName);
                }

                $.each(elements, function (index, field) {
                    uiRegistry.async(formPath + '.' + field)(self.doElementBinding.bind(self));
                });
            },

            /**
             * Bind shipping rates request to form element
             *
             * @param {Object} element
             * @param {Boolean} force
             * @param {Number} delay
             */
            doElementBinding: function (element, force, delay) {
                var observableFields = shippingRatesValidationRules.getObservableFields();

                if (element && (observableFields.indexOf(element.index) !== -1 || force)) {
                    if (element.index !== postcodeElementName) {
                        this.bindHandler(element, delay);
                    }
                }

                if (element.index === postcodeElementName) {
                    this.bindHandler(element, delay);
                    postcodeElement = element;
                }
            },

            /**
             * @param {Object} element
             * @param {Number} delay
             */
            bindHandler: function (element, delay) {
                var self = this;

                delay = typeof delay === 'undefined' ? self.validateDelay : delay;

                if (element.component.indexOf('/group') !== -1) {
                    $.each(element.elems(), function (index, elem) {
                        self.bindHandler(elem);
                    });
                } else {
                    element.on('value', function () {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function () {
                            if (self.postcodeValidation()) {
                                self.validateFields();
                            }
                        }, delay);

                    });
                    observedElements.push(element);
                }
            },

            /**
             * @return {*}
             */
            postcodeValidation: function () {
                var countryId = $('select[name="country_id"]').val(),
                    validationResult,
                    warnMessage;

                if (postcodeElement == null || postcodeElement.value() == null) {
                    return true;
                }

                postcodeElement.warn(null);
                validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

                if (!validationResult) {
                    warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                    if (postcodeValidator.validatedPostCodeExample.length) {
                        warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                    }
                    warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                    postcodeElement.warn(warnMessage);
                }

                return validationResult;
            },

            /**
             * Convert form data to quote address and validate fields for shipping rates
             */
            validateFields: function () {
                this.saveBillingAddress();
            },

            /**
             * Collect observed fields data to object
             *
             * @returns {*}
             */
            collectObservedData: function () {
                var observedValues = {};

                $.each(observedElements, function (index, field) {
                    observedValues[field.dataScope] = field.value();
                });

                return observedValues;
            },

            /**
             * @inheritDoc
             */
            onAddressChange: function (address) {
                if (address.customerAddressId !== null) {
                    selectBillingAddress(address);
                    this.isNewAddressLinkVisible(true);
                }
            },

            isBillingSelected: ko.computed(function () {
                    return quote.billingAddress() ?
                        quote.billingAddress().customerAddressId
                        : null;
                }
            ),

            /**
             * @inheritDoc
             */
            canUseShippingAddress: ko.computed(function () {
                var enableDifferentShippingAddress = parseInt(window.checkoutConfig.general.enableDifferentShippingAddress);

                return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling()
                    && enableDifferentShippingAddress;
            }),

            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectShippingAddress(quote.billingAddress());

                    if (window.checkoutConfig.reloadOnBillingAddress ||
                        !window.checkoutConfig.displayBillingOnPaymentMethod
                    ) {
                        setBillingAddressAction(globalMessageList);
                    }

                    setTimeout(function () {
                        // again sticky as we have left block smaller
                        $('.glc-right-col').mage('sticky', {
                            container: '#maincontent'
                        });
                    }, 1000);
                } else {
                    var addressData = this.source.get('shippingAddress');

                    selectShippingAddress(createShippingAddress(addressData));

                    setTimeout(function () {
                        // again sticky as we have left block larger
                        $('.glc-right-col').mage('sticky', {
                            container: '#maincontent'
                        });
                    }, 1000);
                }

                return true;
            },

            addNewAddressClick: function () {
                var addressData = this.source.get('billingAddress');

                this.isNewAddressLinkVisible(false);
                selectBillingAddress(createBillingAddress(addressData));

                // again sticky as we have left block larger
                $('.glc-right-col').mage('sticky', {
                    container: '#maincontent'
                });
            }
        });
    }
);
