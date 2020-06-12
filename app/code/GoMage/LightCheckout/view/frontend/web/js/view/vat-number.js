define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/element/abstract',
        'Magento_Checkout/js/model/payment/additional-validators',
        'GoMage_LightCheckout/js/action/vat-number-check',
        'mage/translate',
        'uiRegistry',
        'GoMage_LightCheckout/js/action/update-sections'
    ],
    function (
        $,
        ko,
        Component,
        additionalValidators,
        vatNumberCheckAction,
        $t,
        uiRegistry,
        updateSectionAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                imports: {
                    onCountryUpdate: '${ $.parentName }.country_id:value'
                }
            },
            isEnabledVatCheck: window.checkoutConfig.vatTax.enabled,
            checkboxText: ko.observable(window.checkoutConfig.vatTax.checkboxText),
            errorMessage: $t('Please specify valid VAT number.'),

            initialize: function () {
                var checkboxSettings = parseInt(window.checkoutConfig.vatTax.checkboxSettings),
                    self = this;

                this._super();

                if (parseInt(this.isEnabledVatCheck)) {
                    if (checkboxSettings === 0) {
                        this.isCheckboxVisible(false);
                        this.isCheckboxChecked(true);
                    } else if (checkboxSettings === 1) {
                        this.isCheckboxVisible(true);
                        this.isCheckboxChecked(true);
                    } else if (checkboxSettings === 2) {
                        this.isCheckboxVisible(true);
                        this.isCheckboxChecked(false);
                    }
                } else {
                    this.isCheckboxVisible = false;
                }

                additionalValidators.registerValidator(this);

                this.isCheckboxChecked.subscribe(function () {
                    var country = uiRegistry.get(self.parentName + '.' + 'country_id');
                    self.checkVat(self.value(), country.value());
                });
            },

            initObservable: function () {
                this._super()
                    .observe({
                        verifiedLabelText: '',
                        notVerifiedLabelText: '',
                        isCheckboxVisible: true,
                        isCheckboxChecked: true
                    });

                return this;
            },

            onFocusOut: function (element) {
                var country = uiRegistry.get(this.parentName + '.' + 'country_id');

                this.checkVat(element.value(), country.value());
            },

            checkVat: function (vatNumber, country) {
                var result,
                    self = this;

                if (parseInt(this.isEnabledVatCheck)) {
                    if (vatNumber && country) {
                        result = vatNumberCheckAction(vatNumber, country, this.isCheckboxChecked());
                        result.success(function (result) {
                            if (result) {
                                self.setVerified()
                            } else {
                                self.setNotVerified();
                            }
                            updateSectionAction();
                        });
                    } else if (vatNumber && !country) {
                        this.setNotVerified();
                    } else if (!vatNumber) {
                        this.clearAdditionalLabels();
                    }

                    if (this.customScope === 'shippingAddress') {
                        var billing = uiRegistry.get(this.name.replace(/shipping/g, 'billing'));
                        if (billing) {
                            billing.value(this.value())
                        }
                    } else if (this.customScope === 'billingAddress') {
                        var shipping = uiRegistry.get(this.name.replace(/billing/g, 'shipping'));
                        if (shipping) {
                            shipping.value(this.value())
                        }
                    }
                }
            },

            setVerified: function () {
                this.notVerifiedLabelText('');
                this.verifiedLabelText('(' + $t('Verified') + ')');
                this.error('');
            },

            setNotVerified: function () {
                this.verifiedLabelText('');
                this.notVerifiedLabelText('(' + $t('Not Verified') + ')');
                this.error(this.errorMessage);
            },

            clearAdditionalLabels: function () {
                this.verifiedLabelText('');
                this.notVerifiedLabelText('');
                this.error('');
            },

            onCountryUpdate : function (country) {
                var vatNumber = this.value();

                this.checkVat(vatNumber, country);
            },

            validate: function () {
                var result = false;

                if (parseInt(this.isEnabledVatCheck)) {
                    if (this.isCheckboxVisible() && this.isCheckboxChecked() && this.value() === '') {
                        this.error(this.errorMessage);
                    } else if (this.notVerifiedLabelText() === '') {
                        result = true;
                    }
                } else {
                    result = true;
                }

                return result;
            }
        })
    }
);
