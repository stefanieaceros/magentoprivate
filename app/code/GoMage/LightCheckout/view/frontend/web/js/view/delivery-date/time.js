
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'moment',
    'mageUtils'
], function (_, registry, Select, moment, utils) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                updateValues: '${ $.parentName }.selectDate:value'
            }
        },

        /**
         * @param {String} value
         */
        updateValues: function (value) {
            var deliveryDaysWithTime = window.checkoutConfig.deliveryDate.deliveryDaysWithTime,
                deliveryHoursHelper = window.checkoutConfig.deliveryDate.deliveryHoursHelper,
                dateFormat = window.checkoutConfig.deliveryDate.dateFormat,
                day = null,
                options = {};

            if (value) {
                day = moment(value, utils.convertToMomentFormat(dateFormat)).day();
                if (deliveryDaysWithTime[day]) {
                    _.each(deliveryDaysWithTime[day], function (hour, key) {
                        options[key] = deliveryHoursHelper[hour];
                    });
                }
            }

            this.setOptions(options);
        }
    });
});

