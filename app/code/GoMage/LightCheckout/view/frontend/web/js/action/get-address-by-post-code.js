
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'GoMage_LightCheckout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        quote,
        resourceUrlManager,
        storage,
        errorProcessor,
        fullScreenLoader,
        uiRegistry,
        customerData
    ) {
        'use strict';

        return function (postcode, parentScope) {
            var payload = {
                postcode: postcode
            };

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForGetAddressByPostCode(),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    var city, countryId, regionId;

                    if (response.redirect_url) {
                        customerData.invalidate(['cart']);
                        window.location.href = response.redirect_url;
                        return;
                    }

                    if (response.city) {
                        city = uiRegistry.get(parentScope + '.city');
                        if (city) {
                            city.value(response.city);
                        }
                    }

                    if (response.country_id) {
                        countryId = uiRegistry.get(parentScope + '.country_id');
                        if (countryId) {
                            countryId.value(response.country_id);
                        }
                    }
                    if (response.region_id) {
                        regionId = uiRegistry.get(parentScope + '.region_id');
                        if (regionId) {
                            regionId.value(response.region_id);
                        }
                    }

                    if (response.enable_fields === true || response.enable_fields === false) {
                        if (city) {
                            city.disabled(!response.enable_fields);
                        }
                        if (countryId) {
                            countryId.disabled(!response.enable_fields);
                        }
                        if (regionId) {
                            regionId.disabled(!response.enable_fields);
                        }
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
