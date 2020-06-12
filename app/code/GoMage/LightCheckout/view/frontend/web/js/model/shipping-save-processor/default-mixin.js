define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'uiRegistry'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    payloadExtender,
    uiRegistry
) {
    'use strict';

    return function (originalObject) {
        /**
         * Override this method to avoid updating totals on the checkout page when place order is started
         *
         * @return {jQuery.Deferred}
         */
        originalObject.saveShippingInformation = function () {
            if (!quote.shippingMethod()) return; // if shipping method is not needed

            var payload;

            if (!quote.billingAddress() && quote.shippingAddress().canUseForBilling()) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod()['method_code'],
                    'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                }
            };

            payloadExtender(payload);

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    uiRegistry.get("checkout.sidebar.placeOrderButton", function (placeOrderButton) {
                        if (!placeOrderButton.isPlaceOrderButtonClicked()) {
                            quote.setTotals(response.totals);
                            paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
                            fullScreenLoader.stopLoader();
                        }
                    });
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        };

        return originalObject;
    }
});
