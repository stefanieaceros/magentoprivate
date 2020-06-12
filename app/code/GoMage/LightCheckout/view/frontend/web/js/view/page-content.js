define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            text: window.checkoutConfig.general.pageContent
        }
    });
});
