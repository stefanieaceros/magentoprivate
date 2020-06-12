define(
    [
        'uiComponent',
        'GoMage_LightCheckout/js/action/update-quote-item'
    ],
    function (Component, updateQuoteItemAction) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/summary/item/details/increase-item-qty'
            },
            increaseItemQty: function (item) {
                item.qty++;

                updateQuoteItemAction(item);
            }
        });
    }
);
