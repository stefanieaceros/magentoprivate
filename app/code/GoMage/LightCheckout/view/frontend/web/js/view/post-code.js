/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/post-code',
    'GoMage_LightCheckout/js/action/get-address-by-post-code',
    'Magento_Checkout/js/model/postcode-validator'
], function ($, _, registry, PostCode, getAddressByPostCodeAction, postcodeValidator) {
    'use strict';

    return PostCode.extend({
        onFocusOut: function (element) {
            var countryId = $('select[name="country_id"]').val(),
            validationResult = postcodeValidator.validate(element.value(), countryId);

            if (validationResult) {
                getAddressByPostCodeAction(element.value(), this.parentName)
            }
        }
    });
});
