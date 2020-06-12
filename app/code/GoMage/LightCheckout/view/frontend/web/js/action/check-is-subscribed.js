/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'mage/storage',
        'Magento_Checkout/js/model/url-builder'
    ],
    function (storage, urlBuilder) {
        'use strict';

        return function (deferred, email) {
            return storage.post(
                urlBuilder.createUrl('/light_checkout/is-customer-subscribed-for-newsletter', {}),
                JSON.stringify({
                    customerEmail: email
                }),
                false
            ).done(
                function (isSubscribed) {
                    if (isSubscribed) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                }
            ).fail(
                function () {
                    deferred.reject();
                }
            );
        };
    }
);
