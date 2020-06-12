define(
    [
        'jquery',
        'uiComponent',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/model/customer'
    ],
    function (
        $,
        Component,
        uiRegistry,
        quote,
        additionalValidators,
        customer
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/create-account'
            },
            passwordMinLength: window.checkoutConfig.passwordSettings.minimumPasswordLength,
            passwordMinCharacterSets: window.checkoutConfig.passwordSettings.requiredCharacterClassesNumber,

            initialize: function () {
                this._super();

                additionalValidators.registerValidator(this);
            },

            initObservable: function () {
                var shouldCreateAccountBeVisible = false,
                    shouldCheckboxBeVisible = false,
                    shouldIsCreateAnAccountCheckboxChecked = parseInt(
                        window.checkoutConfig.registration.isCreateAnAccountCheckboxChecked || 0
                    ),
                    autoRegistration = parseInt(window.checkoutConfig.registration.autoRegistration || 0),
                    checkoutMode = parseInt(window.checkoutConfig.registration.checkoutMode || 0);

                if (autoRegistration === 0 && !customer.isLoggedIn() && checkoutMode === 0) {
                    shouldCreateAccountBeVisible = true;
                    shouldCheckboxBeVisible = true;
                }

                if (checkoutMode === 1 && !customer.isLoggedIn()) {
                    shouldCreateAccountBeVisible = true;
                    shouldCheckboxBeVisible = false;
                    shouldIsCreateAnAccountCheckboxChecked = true;
                }

                if (customer.isLoggedIn() || (autoRegistration === 1 && checkoutMode !== 1)) {
                    shouldCreateAccountBeVisible = false;
                    shouldCheckboxBeVisible = false;
                    shouldIsCreateAnAccountCheckboxChecked = false;
                }

                this._super()
                    .observe({
                        isCreateAccountVisible: shouldCreateAccountBeVisible,
                        isCheckboxVisible: shouldCheckboxBeVisible,
                        isCreateAnAccountCheckboxChecked: shouldIsCreateAnAccountCheckboxChecked
                    });

                return this;
            },

            onCreateAccountClick: function (value) {
                this.isPasswordVisible(value);
            },

            validate: function () {
                var result, passwordSelector, confirm, password;

                if (this.isCreateAccountVisible() && this.isCreateAnAccountCheckboxChecked()) {
                    passwordSelector = $('#account-password');
                    passwordSelector.parents('form').validation();

                    password = !!passwordSelector.valid();
                    confirm = !!$('#account-password-confirmation').valid();

                    result = password && confirm;
                } else {
                    result = true;
                }

                return result;
            },
            
            getLabel: function (label) {
                if (parseInt(window.checkoutConfig.addressFields.keepInside) === 1) {
                    label = '';
                }

                return label;
            },

            getPlaceholder: function (placeholder) {
                if (parseInt(window.checkoutConfig.addressFields.keepInside) !== 1) {
                    placeholder = '';
                }

                return placeholder;
            },

            afterRenderCreateAnAccount: function () {
                if (uiRegistry.get('checkout.customer-email').isPasswordVisible()
                    && parseInt(window.checkoutConfig.registration.checkoutMode || 0) !== 1
                ) {
                    this.isCreateAccountVisible(false);
                }
            }
        })
    }
);
