
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'GoMage_LightCheckout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        quote,
        resourceUrlManager,
        storage,
        errorProcessor,
        methodConverter,
        paymentService,
        fullScreenLoader,
        customerData
    ) {
        'use strict';

        return function () {
            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForUpdateSections(quote.getQuoteId())
            ).done(
                function (response) {
                    if (response.redirect_url) {
                        customerData.invalidate(['cart']);
                        window.location.href = response.redirect_url;
                        return;
                    }
                    if (response.totals) {
                        quote.setTotals(response.totals);
                    }

                    if (response.payment_methods) {
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
