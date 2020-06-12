define(
    [
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'GoMage_LightCheckout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Customer/js/customer-data'
    ],
    function (
        quote,
        storage,
        errorProcessor,
        fullScreenLoader,
        methodConverter,
        paymentService,
        resourceUrlManager,
        shippingService,
        customerData
    ) {
        'use strict';

        return function (item) {
            var serviceUrl = resourceUrlManager.getUrlForUpdateItem(quote.getQuoteId()),
                payload = {
                    item: item
                };

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    if (response.redirect_url) {
                        customerData.invalidate(['cart']);
                        window.location.href = response.redirect_url;
                        return;
                    } else if (response.error) {
                        // if requested quantity is not available
                        item.qty--;
                        return;
                    }
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    if (response.shipping_methods && !quote.isVirtual()) {
                        shippingService.setShippingRates(response.shipping_methods);
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(function () {
                fullScreenLoader.stopLoader();
            });
        };
    }
);
