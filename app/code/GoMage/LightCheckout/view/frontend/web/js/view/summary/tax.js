define(
    [
        'Magento_Tax/js/view/checkout/summary/tax'
    ],
    function (Component) {
        return Component.extend({
            isFullMode: function () {
                return true;
            }
        });
    }
);
