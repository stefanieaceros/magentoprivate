define([
    'jquery',
    'uiClass',
    'uiRegistry'
], function ($, Class, registry) {
    'use strict';

    var addressFields = ['street', 'country_id', 'city', 'region', 'region_id', 'postcode'];
    var googleFieldsMapper = {
        street_number: [
            {
                source: 'short_name',
                destination: 'street'
            }
        ],
        route: [
            {
                source: 'long_name',
                destination: 'street'
            }
        ],
        administrative_area_level_2: [
            {
                source: 'short_name',
                destination: 'street'
            }
        ],
        locality: [
            {
                source: 'long_name',
                destination: 'city'
            }
        ],
        postal_town: [
            {
                source: 'long_name',
                destination: 'city'
            }
        ],
        administrative_area_level_1: [
            {
                source: 'long_name',
                destination: 'region_id'
            },
            {
                source: 'long_name',
                destination: 'region'
            }
        ],
        country: [
            {
                source: 'short_name',
                destination: 'country_id'
            }
        ],
        postal_code: [
            {
                source: 'short_name',
                destination: 'postcode'
            }
        ]
    };

    return Class.extend({

        /**
         * @inheritDoc
         */
        initialize: function (fieldsetName) {
            this._super();

            this.initPresentAddressElements(fieldsetName)
                .initAutoComplete();

            return this;
        },

        /**
         * add to array address fields which are presented in checkout to know what to fill.
         *
         * @param fieldsetName
         * @returns {exports}
         */
        initPresentAddressElements: function (fieldsetName) {
            var self = this;
            this.presentAddressFields = {};

            $.each(addressFields, function (index, fieldName) {
                var addressField = registry.async(fieldsetName + '.' + fieldName)();

                if (fieldName === 'street') {
                    $.each(addressField.elems(), function (key, elem) {
                        if (key === 0) {
                            addressField = elem;
                            self.inputSelector = document.getElementById(elem.uid);
                            return false;
                        }
                    });
                }
                if (typeof addressField !== 'undefined') {
                    self.presentAddressFields[fieldName] = addressField;
                }
            });

            return this;
        },

        /**
         *
         * @returns {exports}
         */
        initAutoComplete: function () {
            if (this.inputSelector) {
                var options = {
                    types: ['geocode']
                };

                this.autoComplete = new google.maps.places.Autocomplete(this.inputSelector, options);
                this.autoComplete.addListener('place_changed', this.onPlaceChanged.bind(this));
            }

            return this;
        },

        /**
         * Fire when autoComplete change address.
         */
        onPlaceChanged: function () {
            var place = this.autoComplete.getPlace();
            var addressFieldsToUpdate = this.initEmptyFields();

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];

                if (googleFieldsMapper.hasOwnProperty(addressType)) {
                    $.each(googleFieldsMapper[addressType], function (key, mapTo) {
                        var addressValue = place.address_components[i][mapTo.source];

                        if (mapTo.destination === 'street') {
                            if (addressFieldsToUpdate.street !== '') {
                                addressFieldsToUpdate.street += ', ';
                            }
                            addressFieldsToUpdate.street += addressValue;
                        } else {
                            addressFieldsToUpdate[mapTo.destination] = addressValue;
                        }
                    });
                }
            }

            if (place.hasOwnProperty('name')) {
                addressFieldsToUpdate.street = place.name;
            }

            this.updateAddress(addressFieldsToUpdate);
        },

        /**
         * Update address fileds on checkout.
         *
         * @param fieldsToUpdate
         */
        updateAddress: function (fieldsToUpdate) {
            var self = this;
            $.each(this.presentAddressFields, function (index, element) {
                if (element.visible() && fieldsToUpdate.hasOwnProperty(index)) {
                    if (fieldsToUpdate[index]) {
                        if (index == 'region_id') {
                            $.each(element.options(), function (key, option) {
                                if (fieldsToUpdate[index] == option.label) {
                                    element.value(option.value);
                                    return false;
                                }
                            });
                        } else {
                            element.value(fieldsToUpdate[index]);
                            if (index == 'street') {
                                self.inputSelector.value = fieldsToUpdate[index];
                            }
                        }
                    }
                }
            });
        },

        /**
         *
         * @returns {{}}
         */
        initEmptyFields: function () {
            var emptyFields = {};
            $.each(addressFields, function (index, fieldName) {
                emptyFields[fieldName] = '';
            });

            return emptyFields;
        }
    });
});