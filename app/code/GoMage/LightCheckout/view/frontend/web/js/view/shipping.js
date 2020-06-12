define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Customer/js/model/address-list',
        'mage/translate',
        'underscore',
        'GoMage_LightCheckout/js/action/update-sections',
        'GoMage_LightCheckout/js/model/address/auto-complete-register',
        'rjsResolver',
        'Magento_Checkout/js/action/create-shipping-address'
    ],
    function (
        ko,
        $,
        Component,
        quote,
        registry,
        checkoutData,
        shippingRatesValidator,
        setShippingInformationAction,
        selectShippingAddress,
        additionalValidators,
        customer,
        addressConverter,
        addressList,
        $t,
        _,
        updateSectionAction,
        autoCompleteRegister,
        rjsResolver,
        createShippingAddress
    ) {
        'use strict';
        var addressOptions = addressList().filter(function (address) {
            return address.getType() == 'customer-address';
        });
        return Component.extend({
            defaults: {
                imports: {
                    isPlaceOrderButtonClicked: 'checkout.sidebar.placeOrderButton:isPlaceOrderButtonClicked',
                    isAddressSameAsShipping: 'checkout.billingAddress:isAddressSameAsShipping' // for update UI (shipping address block)
                },
                exports: {
                    isAddressSameAsShipping: 'checkout.billingAddress:isAddressSameAsShipping' // for update UI (shipping address block)
                },
                isAddressSameAsShipping: ko.observable()
            },
            addressOptions: addressOptions,

            /**
             * @inheritDoc
             */
            initialize: function () {
                var fieldsetName = 'checkout.shippingAddress.shipping-address-fieldset';

                if (!checkoutData.getSelectedShippingRate() && window.checkoutConfig.general.defaultShippingMethod) {
                    checkoutData.setSelectedShippingRate(window.checkoutConfig.general.defaultShippingMethod);
                }

                this._super();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                    shippingRatesValidator.initFields(fieldsetName);
                });

                additionalValidators.registerValidator(this);

                rjsResolver(this.registerAutoComplete.bind(this));

                return this;
            },

            registerAutoComplete: function () {
                autoCompleteRegister.register('shipping');
            },

            /**
             * @inheritDoc
             */
            initObservable: function () {
                this._super()
                    .observe({
                        isNewAddressLinkVisible: customer.isLoggedIn()
                    });

                // check if not only new address present
                if (this.addressOptions.length > 0) {
                    for (var i = 0; i < this.addressOptions.length; i++) {
                        if (this.addressOptions[i].isDefaultShipping()) {
                            this.onAddressChange(this.addressOptions[i]);
                            break;
                        }
                    }
                }

                quote.shippingMethod.subscribe(function (oldValue) {
                    this.currentMethod = oldValue;
                }, this, 'beforeChange');

                quote.shippingMethod.subscribe(function (newValue) {
                    var isMethodChange = ($.type(this.currentMethod) !== 'object') ? true : this.currentMethod.method_code;
                    if ($.type(newValue) === 'object' && (isMethodChange !== newValue.method_code)) {
                        setShippingInformationAction();
                    } else if (typeof this.isPlaceOrderButtonClicked !== 'undefined' && !this.isPlaceOrderButtonClicked) {
                        updateSectionAction();
                    }
                }, this);

                return this;
            },

            isShippingSelected: ko.computed(function () {
                    return quote.shippingAddress() ?
                        quote.shippingAddress().customerAddressId
                        : null;
                }
            ),

            /**
             * @returns {string}
             */
            getShippingMethodsTemplate: function () {
                return 'GoMage_LightCheckout/form/shipping-methods';
            },

            /**
             * @inheritDoc
             */
            canUseShippingAddress: ko.computed(function () {
                var enableDifferentShippingAddress = parseInt(window.checkoutConfig.general.enableDifferentShippingAddress);

                return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling()
                    && enableDifferentShippingAddress;
            }),

            /**
             * @param {Object} address
             * @return {*}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * @returns {boolean}
             */
            validate: function () {
                if (quote.isVirtual()) {
                    return true;
                }

                var shippingMethodValidationResult = true,
                    shippingAddressValidationResult = true,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn(),
                    isCustomerHasAddresses = true;

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method.');
                    shippingMethodValidationResult = false;
                }

                if (!customer.isLoggedIn() && !$('#customer-email-error').is(':visible')) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (customer.isLoggedIn()) { // if customer is not logged in customer.customerData.addresses doesn't exist
                    if (typeof customer.customerData.addresses.length !== 'undefined' &&
                        customer.customerData.addresses.length === 0) {
                        isCustomerHasAddresses = false;
                    }
                } else {
                    isCustomerHasAddresses = false;
                }

                if (!$('.glc-switcher.billing-address-same-as-shipping-block input[type=checkbox]').is(':checked')) {
                    if (!this.isNewAddressLinkVisible() || !isCustomerHasAddresses) {
                        this.source.set('params.invalid', false);
                        this.source.trigger('shippingAddress.data.validate');

                        if (this.source.get('shippingAddress.custom_attributes')) {
                            this.source.trigger('shippingAddress.custom_attributes.data.validate');
                        }

                        if (this.source.get('params.invalid')) {
                            shippingAddressValidationResult = false;
                        }

                        var addressData = addressConverter.formAddressDataToQuoteAddress(
                            this.source.get('shippingAddress')
                        );

                        if (customer.isLoggedIn() && this.addressOptions.length === 0) {
                            this.saveInAddressBook = 1;
                        }

                        addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                        selectShippingAddress(addressData);
                    }
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();
                }

                return shippingMethodValidationResult && shippingAddressValidationResult && emailValidationResult;
            },

            /**
             * @param {Object} address
             */
            onAddressChange: function (address) {
                if (address) {
                    if (address.customerAddressId !== null) {
                        selectShippingAddress(address);
                        this.isNewAddressLinkVisible(true);
                    }
                }
            },

            addNewAddressClick: function () {
                var addressData = this.source.get('shippingAddress');

                this.isNewAddressLinkVisible(false);
                selectShippingAddress(createShippingAddress(addressData));

                // again sticky as we have left block larger
                $('.glc-right-col').mage('sticky', {
                    container: '#maincontent'
                });
            }
        });
    }
);
