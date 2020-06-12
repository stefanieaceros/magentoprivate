define(
    [
        'uiRegistry',
        'underscore'
    ],
    function (uiRegistry, _) {
        'use strict';

        return function (paymentDefault) {

            return paymentDefault.extend({
                initChildren: function () {
                    this.messageContainer = uiRegistry.get('checkout.errors').messageContainer;

                    return this;
                }
            });
        }
    });
