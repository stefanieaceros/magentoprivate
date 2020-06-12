var config = {
    map: {
        '*': {
            'Magento_CheckoutAgreements/js/model/agreements-assigner': 'GoMage_LightCheckout/js/model/agreements-assigner'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/default': {
                'GoMage_LightCheckout/js/view/payment/default-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'GoMage_LightCheckout/js/model/shipping-save-processor/default-mixin': true
            },
            'Magento_Checkout/js/model/place-order': {
                'GoMage_LightCheckout/js/model/place-order-mixin': true
            }
        }
    }
};
