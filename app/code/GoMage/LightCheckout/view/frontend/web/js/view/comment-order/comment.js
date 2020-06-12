define([
    'ko',
    'jquery',
    'uiComponent'
], function (ko, $, Component) {
    'use strict';

    return Component.extend({
        defaults: {},

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();
            return this;
        }
    })
});
