define(
    [
        'Magento_Checkout/js/view/summary/totals'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @inheritDoc
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);
