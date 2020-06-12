
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Ui/js/model/messageList'
    ],
    function ($, Component, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'GoMage_LightCheckout/success/subscribe-to-newsletter',
                newsletterSubscribed: false,
                subscribingStarted: false,
                isFormVisible: true
            },

            /**
             * @inheritDoc
             */
            initObservable: function () {
                this._super()
                    .observe('newsletterSubscribed')
                    .observe('isFormVisible')
                    .observe('subscribingStarted');

                return this;
            },

            /**
             * @return {*}
             */
            getEmailAddress: function () {
                return this.email;
            },

            subscribeToNewsletter: function () {
                this.subscribingStarted(true);
                $.post(
                    this.subscribeToNewsletterUrl
                ).done(
                    function (response) {
                        if (response.errors == false) {
                            this.newsletterSubscribed(true)
                        } else {
                            messageList.addErrorMessage(response);
                        }
                        this.isFormVisible(false);
                    }.bind(this)
                ).fail(
                    function (response) {
                        this.newsletterSubscribed(false);
                        this.isFormVisible(false);
                        messageList.addErrorMessage(response);
                    }.bind(this)
                );
            }
        });
    }
);
