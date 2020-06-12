define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-shipping-information',
        'GoMage_LightCheckout/js/action/save-additional-information'
    ],
    function (
        $,
        _,
        ko,
        Component,
        additionalValidators,
        setShippingInformation,
        saveAdditionalInformation
    ) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/place-order',
                isPlaceOrderButtonClicked: ko.observable(false)
            },
            placeOrderPaymentMethodSelector: '#co-payment-form .payment-method._active button.action.primary.checkout',

            placeOrder: function () {
                var self = this;
                self.isPlaceOrderButtonClicked(false); // Save shipping address only 1 time on validation step

                if (additionalValidators.validate()) {
                    self.isPlaceOrderButtonClicked(true);
                    this.prepareToPlaceOrder().done(function () {
                        self._placeOrder();
                    }).fail(function () {
                        self.isPlaceOrderButtonClicked(false);
                    });
                } else {
                    self.isPlaceOrderButtonClicked(false);
                }

                return this;
            },

            _placeOrder: function () {
                $(this.placeOrderPaymentMethodSelector).trigger('click');
            },

            prepareToPlaceOrder: function () {
                return $.when(setShippingInformation()).done(function () {
                    $.when(saveAdditionalInformation()).done(function () {
                        $("body").animate({scrollTop: 0}, "slow");
                    });
                });
            }
        });
    }
);
