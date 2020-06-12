define(
    [
        'mage/utils/wrapper',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (wrapper, fullScreenLoader) {
        'use strict';

        return function (placeOrderServiceFunction) {
            return wrapper.wrap(placeOrderServiceFunction, function (
                originalPlaceOrderServiceFunction, serviceUrl, payload, messageContainer
            ) {
                return originalPlaceOrderServiceFunction(serviceUrl, payload, messageContainer).fail(
                    function (response) {
                        fullScreenLoader.stopLoader();
                    }
                );
            });
        };
    }
);
