define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'GoMage_LightCheckout/js/light-checkout-data',
        'uiRegistry',
        'mageUtils',
        'moment'
    ],
    function (
        $,
        ko,
        Component,
        quote,
        lightCheckoutData,
        registry,
        utils,
        moment
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/delivery-date',
                useForShippingMethod: ko.observable(true)
            },
            deliveryDateText: window.checkoutConfig.deliveryDate.deliveryDateText,
            displayDeliveryDateText: window.checkoutConfig.deliveryDate.displayDeliveryDateText,

            initialize: function () {
                var self = this,
                    configFormat = window.checkoutConfig.deliveryDate.dateFormat,
                    dateFormat = utils.normalizeDate(configFormat),
                    defaultFormat = 'y-MM-dd',
                    formData = lightCheckoutData.getDeliveryDate();

                this._super();

                quote.shippingMethod.subscribe(function (newValue) {
                    var newShipping = newValue['carrier_code'] + '_' + newValue['method_code'];
                    if (window.checkoutConfig.deliveryDate.shippingMethods.indexOf(newShipping) === -1) {
                        self.useForShippingMethod(false);
                    } else {
                        self.useForShippingMethod(true);
                    }
                }, this);

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    if (formData) {
                        if (formData.selectDate !== undefined && formData.selectDate !== '') {
                            var momentDate = moment(formData.selectDate, dateFormat);
                            _.extend(
                                formData,
                                {'selectDate': momentDate.format(utils.normalizeDate(defaultFormat))}
                            );
                        }

                        checkoutProvider.set(
                            'deliveryDate',
                            _.extend({}, checkoutProvider.get('deliveryDate'), formData)
                        );
                    }

                    //save in storage delivery date data.
                    checkoutProvider.on('deliveryDate', function (formData) {
                        if (formData.selectDate !== '') {
                            lightCheckoutData.setDeliveryDate(formData);
                        }
                    });

                });
            }
        });
    }
);
