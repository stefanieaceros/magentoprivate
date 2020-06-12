define(
    [
        'uiComponent',
        'GoMage_LightCheckout/js/action/update-quote-item'
    ],
    function (Component, updateQuoteItemAction) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/summary/item/details/decrease-item-qty'
            },
            decreaseItemQty: function (item) {
                var qty = item.qty;
                qty--;

                if (qty >= 0) {
                    item.qty = qty;
                    updateQuoteItemAction(item);
                }
            }
        });
    }
);
