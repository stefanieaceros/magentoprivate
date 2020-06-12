define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    /**
     * @param url
     * @param windowObj
     */
    window.socialCallback = function (url, windowObj) {

        customerData.invalidate(['customer']);
        customerData.reload(['customer'], true);

        window.location.href = url;

        windowObj.close();
    };

    return Component.extend({});
});
