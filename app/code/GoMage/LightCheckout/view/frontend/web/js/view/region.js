define([
    'underscore',
    'Magento_Ui/js/form/element/region',
    'mageUtils',
    'uiLayout',
    'uiRegistry'
], function (_, Component, utils, layout, registry) {
    'use strict';

    var inputNode = {
        parent: '${ $.$data.parentName }',
        component: 'Magento_Ui/js/form/element/abstract',
        template: '${ $.$data.template }',
        placeholder: '${ $.$data.inputPlaceholder }',
        provider: '${ $.$data.provider }',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.customEntry }',
        customScope: '${ $.$data.customScope }',
        sortOrder: {
            after: '${ $.$data.name }'
        },
        displayArea: 'body',
        label: '${ $.$data.label }'
    };

    return Component.extend({
        initInput: function () {
            layout([utils.template(_.extend(
                inputNode,
                {
                    additionalClasses: this.additionalClasses,
                    tooltip: this.tooltip
                }
            ), this)]);

            return this;
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            if (this.mandatorySetting === 'required') {
                this._super(value);
                this.validation['required-entry'] = true;
                registry.get(this.customName, function (input) {
                    input.validation['required-entry'] = true;
                    input.validation['validate-not-number-first'] = true;
                    input.required(true);
                });
                this.required(true);
            } else if (this.mandatorySetting === 'no_required') {
                this.skipValidation = true;
                this._super(value);
            } else if (this.mandatorySetting === 'use_magento_settings') {
                this._super(value);
            }
        }
    });
});
