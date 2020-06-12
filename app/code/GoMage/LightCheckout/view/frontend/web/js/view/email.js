define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/form/element/email',
    'Magento_Customer/js/action/check-email-availability',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Customer/js/model/customer',
    'uiRegistry',
    'GoMage_LightCheckout/js/action/check-is-subscribed',
    'GoMage_LightCheckout/js/light-checkout-data'
], function (
    $,
    ko,
    Component,
    checkEmailAvailability,
    checkoutData,
    additionalValidators,
    customer,
    uiRegistry,
    checkIsSubscribed,
    lightCheckoutData
) {
    'use strict';

    return Component.extend({
        customerEmailErrorSelector: '#customer-email-error',
        customerNoteSelector: '#note-customer-email',
        onlyRegistered: ko.observable(false),
        checkoutMode: parseInt(window.checkoutConfig.registration.checkoutMode),
        error: 'Customer with this email does not exist. Please '
            + '<a class="registration-popup-link">register</a>'
            + ' before placing order.',
        errorText: ko.observable(''),
        customerExist: false,
        isSubscribeVisible: ko.observable(true),

        /**
         *
         * @inheritDoc
         */
        initialize: function () {
            this.errorText(this.error);
            this._super();

            if (!customer.isLoggedIn() && this.checkoutMode === 1) {
                this.onlyRegistered(true);
                additionalValidators.registerValidator(this);
            }

            return this;
        },

        initConfig: function () {
            this._super();

            if (!customer.isLoggedIn() && this.checkoutMode === 1) {
                if (this.isPasswordVisible) {
                    this.customerExist = true;
                    this.errorText('');
                } else {
                    var createAnAccount = uiRegistry.get('checkout.customer-email.createAccount');

                    if (createAnAccount) {
                        createAnAccount.isCreateAccountVisible(false);
                    }
                }
                this.isPasswordVisible = false;
            }
            if(!this.isNewsletterEnable()){
                this.isSubscribeVisible(false);
            }
        },

        /**
         * Resolves an initial state of a login form.
         *
         * @returns {Boolean} - initial visibility state.
         */
        resolveInitialPasswordVisibility: function () {
           if (checkoutData.getInputFieldEmailValue() !== '') {
                return checkoutData.getInputFieldEmailValue() === checkoutData.getCheckedEmailValue();
           }

            return false;
        },

        /**
         * Check email existing.
         */
        checkEmailAvailability: function () {
            var self = this;

            this.validateRequest();
            this.isEmailCheckComplete = $.Deferred();
            this.isLoading(true);
            this.checkRequest = checkEmailAvailability(this.isEmailCheckComplete, this.email());

            $.when(this.isEmailCheckComplete).done(function () {
                var createAnAccount = uiRegistry.get('checkout.customer-email.createAccount');
                this.isPasswordVisible(false);

                if (createAnAccount) {
                    if (this.checkoutMode !== 1) {
                        createAnAccount.isCreateAccountVisible(true);
                    } else {
                        createAnAccount.isCreateAccountVisible(false);
                    }
                }

                if (!customer.isLoggedIn() && this.checkoutMode === 1) {
                    self.showErrorText();
                    this.isPasswordVisible(false);
                }
                this.customerExist = false;
            }.bind(this)).fail(function () {
                var createAnAccount = uiRegistry.get('checkout.customer-email.createAccount');
                if (this.checkoutMode !== 1) {
                    this.isPasswordVisible(true);
                }

                if (createAnAccount) {
                    if (this.checkoutMode !== 1) {
                        createAnAccount.isCreateAccountVisible(false);
                    } else {
                        createAnAccount.isCreateAccountVisible(true);
                    }
                }

                checkoutData.setCheckedEmailValue(this.email());

                if (!customer.isLoggedIn() && this.checkoutMode === 1) {
                    self.hideErrorText();
                    this.isPasswordVisible(false);
                }
                this.customerExist = true;
            }.bind(this)).always(function () {
                this.isLoading(false);
                this.checkIsSubscribed();
            }.bind(this));
        },

        checkIsSubscribed: function () {
            var isSubscribedCheckComplete = $.Deferred(),
                self = this;

            this.isLoading(true);
            checkIsSubscribed(isSubscribedCheckComplete, this.email());

            $.when(isSubscribedCheckComplete).done(function () {
                self.isSubscribeVisible(false);
                lightCheckoutData.setSubscribedEmailValue(this.email());
            }.bind(this)).fail(function () {
                self.isSubscribeVisible(true);
            }.bind(this)).always(function () {
                this.isLoading(false);
            }.bind(this));
        },

        hideErrorText: function () {
            $(this.customerEmailErrorSelector).hide();
        },

        showErrorText: function () {
            if (!this.errorText()) {
                this.errorText(this.error);
            }
            $(this.customerEmailErrorSelector).html(this.errorText());
            $(this.customerEmailErrorSelector).show();
        },

        validate: function () {
            if (!customer.isLoggedIn() && !this.customerExist) {
                this.showErrorText();

                return false;
            }

            return true;
        },

        /**
         *
         * @inheritdoc
         */
        validateEmail: function (focused) {
            var result = this._super(focused);

            if (this.checkoutMode === 1) {
                if (!customer.isLoggedIn()
                    && !this.customerExist
                    && !$(this.customerEmailErrorSelector).is(':visible')
                ) {
                    this.showErrorText();
                }

                if (!customer.isLoggedIn()
                    && this.customerExist
                    && !$(this.customerEmailErrorSelector).is(':visible')
                ) {
                    this.hideErrorText();
                }
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

        getNewsletterData: function () {
            return  uiRegistry.get('checkout.customer-email.subscribeNewsletter');
        },

        isNewsletterEnable: function () {
            if (this.getNewsletterData()) {
                return true;
            }
            return false;
        }
    });
});
