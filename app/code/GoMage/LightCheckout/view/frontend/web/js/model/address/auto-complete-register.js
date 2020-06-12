define([
    'GoMage_LightCheckout/js/model/address/google-auto-complete'
], function (googleAutoComplete) {
    'use strict';

    var addressType = {
        billing: 'checkout.billingAddress.billing-address-fieldset',
        shipping: 'checkout.shippingAddress.shipping-address-fieldset'
    };

    return {
        register: function (type) {
            if (window.checkoutConfig.autoCompleteStreet.enabled == 1) {
                new googleAutoComplete(addressType[type]);
            }
        }
    };
});
