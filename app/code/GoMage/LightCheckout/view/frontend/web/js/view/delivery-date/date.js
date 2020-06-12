define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/date',
    'moment',
    'underscore'
], function (ko, $, Component, moment, _) {
    'use strict';

    return Component.extend({
        defaults: {
            pickerDefaultDateFormat: window.checkoutConfig.deliveryDate.dateFormat,
            options: {
                showOn: 'both'
            }
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();

            this.options.beforeShowDay = this.beforeShowDay.bind(this);

            return this;
        },

        beforeShowDay: function (date) {
            var isAvailable = this.isDateAvailable(date);

            if (isAvailable && !this.value()) {
                this.value(date);
            }

            return [
                isAvailable,
                '',
                ''
            ];
        },

        /**
         * Check if date should be enabled or disabled.
         *
         * @param date
         * @returns {boolean}
         */
        isDateAvailable: function (date) {
            var isDateAvailableByWeekDay = this.isDateAvailableByWeekDay(date),
                isDateBeforeCurrentDay = this.isDateBeforeCurrentDay(date),
                isDayWorking = this.isDayWorking(date);

            return isDateAvailableByWeekDay && isDateBeforeCurrentDay && isDayWorking;
        },

        /**
         *
         * @param date
         * @returns {boolean}
         */
        isDateAvailableByWeekDay: function (date) {
            var isAvailableDay = true,
                disabledDays = window.checkoutConfig.deliveryDate.disabledDays,
                day = moment(date).day();

            if (Object.values(disabledDays).indexOf(day) !== -1) {
                isAvailableDay = false;
            }

            return isAvailableDay;
        },

        /**
         * If it is day before today need to disable it.
         *
         * @param date
         * @returns {boolean}
         */
        isDateBeforeCurrentDay: function (date) {
            return moment(date).isAfter(moment(), 'day');
        },

        /**
         * Check for non working day and interval for delivery.
         *
         * @param date
         * @returns {boolean}
         */
        isDayWorking: function (date) {
            var isWorking = true,
                disabledDays = window.checkoutConfig.deliveryDate.nonWorkingDays,
                day = moment(date).date(),
                month = moment(date).month();

            _.each(disabledDays,  function (dayWithMonth) {
                if (parseInt(day) == dayWithMonth.day && parseInt(month) == dayWithMonth.month) {
                    isWorking = false;
                }
            });

            return isWorking;
        }
    })
});
