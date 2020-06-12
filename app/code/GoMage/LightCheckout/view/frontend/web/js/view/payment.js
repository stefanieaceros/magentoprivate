
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        ko,
        Component,
        quote,
        stepNavigator,
        additionalValidators,
        checkoutData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/payment'
            },
            isVisible: ko.observable(true),
            errorValidationMessage: ko.observable(false),

            initialize: function () {
                var self = this;

                if (!checkoutData.getSelectedPaymentMethod() && window.checkoutConfig.general.defaultPaymentMethod) {
                    checkoutData.setSelectedPaymentMethod(window.checkoutConfig.general.defaultPaymentMethod);
                }

                this._super();

                stepNavigator.steps.removeAll();

                additionalValidators.registerValidator(this);

                quote.paymentMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                return this;
            },

            validate: function () {
                if (!quote.paymentMethod()) {
                    this.errorValidationMessage('Please specify a payment method.');

                    return false;
                }

                return true;
            }
        });
    }
);
