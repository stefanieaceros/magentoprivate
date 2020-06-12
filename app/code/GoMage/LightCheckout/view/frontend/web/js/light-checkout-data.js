
/**
 * Light checkout adapter for customer data storage
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'light-checkout-data';

    var saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    },
        
    /**
     * @return {*}
     */
    getData = function () {
        var data = storage.get(cacheKey)();

        if ($.isEmptyObject(data)) {
            data = {
                'isAddressSameAsShipping': null,
                'deliveryDate': null
            };
            saveData(data);
        }

        return data;
    };

    return {
        setIsAddressSameAsShipping: function (data) {
            var obj = getData();
            obj.isAddressSameAsShipping = data;
            saveData(obj);
        },

        getIsAddressSameAsShipping: function () {
            return getData().isAddressSameAsShipping;
        },

        setDeliveryDate: function (data) {
            var obj = getData();
            obj.deliveryDate = data;
            saveData(obj);
        },

        getDeliveryDate: function () {
            return getData().deliveryDate;
        },

        getSubscribedEmailValue: function () {
            var obj = getData();

            return obj.subscribedEmailValue ? obj.subscribedEmailValue : '';
        },
        setSubscribedEmailValue: function (email) {
            var obj = getData();

            obj.subscribedEmailValue = email;
            saveData(obj);
        }
    }
});
