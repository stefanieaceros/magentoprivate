/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Checkout/js/view/summary/cart-items',
    'Magento_Checkout/js/view/summary/grand-total',
    'Magento_Checkout/js/model/totals'
], function (ko, Component, grandTotal, totals) {
    'use strict';

    return Component.extend({
        hideProducts: window.checkoutConfig.numberProductInCheckout.hideProducts,
        numberOfProducts: window.checkoutConfig.numberProductInCheckout.numberOfProducts,
        productClasses: 'product-item',

        /**
         *
         * @inheritDoc
         */
        isItemsBlockExpanded: function () {
            return this.hideProducts === false
                || (this.hideProducts === true && this.getCartLineItemsCount() > this.numberOfProducts);
        },

        getTotals: ko.computed(function() {
           return parseFloat(totals.totals()['items_qty']);
        }, this),

        /**
         *
         * @inheritDoc
         */
        setItems: function (items) {
            this.items(items);
        },

        getOrderTotal: ko.computed(function() {
            return grandTotal().getValue();
        }, this)
    });
});
