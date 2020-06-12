define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'uiRegistry'
    ],
    function (
        $,
        ko,
        Component,
        uiRegistry
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/form/comment-order'
            },
            commentConfig:[],
            initialize: function () {
                this._super();
                return this;
            },
            /**
             *
             * @param key
             * @returns {boolean}
             */
            getCommentConfigData: function (key) {
                if(!this.commentConfig.length){
                    this.commentConfig = uiRegistry.get('checkout').configuration.comment_order;
                }
                if(typeof this.commentConfig[key] != 'undefined'){
                    return this.commentConfig[key];
                }
                return  false;
            }
        });
    }
);
