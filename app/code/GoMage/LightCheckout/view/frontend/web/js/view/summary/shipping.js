define(
    [
        'Magento_Tax/js/view/checkout/summary/shipping',
    ],
    function (Component) {
        return Component.extend({
            isFullMode: function () {
                return true;
            }
        });
    }
);
