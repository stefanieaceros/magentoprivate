define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'GoMage_LightCheckout/js/model/resource-url-manager'
    ],
    function (
        $,
        storage,
        errorProcessor,
        fullScreenLoader,
        resourceUrlManager
    ) {
        'use strict';

        return function (vatNumber, country, buyWithoutVat) {
            var serviceUrl = resourceUrlManager.getUrlForCheckVatNumber(),
                payload = {
                    vatNumber: vatNumber,
                    country: country,
                    buyWithoutVat: buyWithoutVat
                };

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
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
