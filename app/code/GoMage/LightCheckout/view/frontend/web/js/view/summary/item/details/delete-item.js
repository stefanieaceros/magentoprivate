define(
    [
        'uiComponent',
        'GoMage_LightCheckout/js/action/remove-quote-item'
    ],
    function (Component, removeQuoteItemAction) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/summary/item/details/delete-item'
            },
            removeItem: function (item) {
                var itemId = item.item_id;

                removeQuoteItemAction(itemId);
            }
        });
    }
);
