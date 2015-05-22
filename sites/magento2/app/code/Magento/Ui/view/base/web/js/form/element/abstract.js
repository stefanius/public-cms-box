/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiComponent',
    'Magento_Ui/js/lib/validation/validator'
], function (_, utils, Component, validator) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: true,
            preview: '',
            focused: false,
            required: false,
            disabled: false,
            tmpPath: 'ui/form/element/',
            tooltipTpl: 'ui/form/element/helper/tooltip',
            input_type: 'input',
            placeholder: '',
            description: '',
            label: '',
            error: '',
            notice: '',

            listens: {
                value: 'onUpdate',
                visible: 'setPreview',
                '<%= provider %>:data.reset': 'reset',
                '<%= provider %>:data.validate': 'validate'
            },

            links: {
                value: '<%= provider %>:<%= dataScope %>'
            },

            exports: {
                visible: '<%= provider %>:config.<%= name %>.visible'
            },

            imports: {
                setPreview: '<%= name %>:value'
            }
        },

        /**
         * Invokes initialize method of parent class, contains initialization
         *     logic
         *
         * @param {Object} config - form element configuration
         */
        initialize: function () {
            _.bindAll(this, 'reset');

            this._super();

            this.initialValue = this.getInititalValue();

            this.value(this.initialValue);

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            var rules = this.validation = this.validation || {};

            this._super();

            this.observe('error disabled focused preview visible')
                .observe({
                    'required': !!rules['required-entry']
                });

            return this;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Abstract} Chainable.
         */
        initProperties: function () {
            var uid = utils.uniqueid();

            this._super();

            _.extend(this, {
                'uid': uid,
                'noticeId': 'notice-' + uid,
                'inputName': utils.serializeName(this.dataScope)
            });

            return this;
        },

        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInititalValue: function () {
            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                return !utils.isEmpty(value = v);
            });

            return utils.isEmpty(value) ? '' : value;
        },

        /**
         * Sets value to preview observable
         *
         * @returns {Abstract} Chainable.
         */
        setPreview: function (value) {
            this.preview(!this.visible() ? '' : value);

            return this;
        },

        /**
         * Sets 'value' as 'hidden' propertie's value, triggers 'toggle' event,
         * sets instance's hidden identifier in params storage based on
         * 'value'.
         *
         * @returns {Abstract} Chainable.
         */
        setVisible: function (isVisible) {
            this.visible(isVisible);

            this.trigger('toggle', isVisible);

            return this;
        },

        /**
         * Returnes unwrapped preview observable.
         *
         * @returns {String} Value of the preview observable.
         */
        getPreview: function () {
            return this.preview();
        },

        /**
         * Checkes if element has addons
         *
         * @returns {Boolean}
         */
        hasAddons: function () {
            return this.addbefore || this.addafter;
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var notEqual = this.value() != this.initialValue;

            return !this.visible() ? false : notEqual;
        },

        hasData: function () {
            return !utils.isEmpty(this.value());
        },

        /**
         * Sets value observable to initialValue property.
         */
        reset: function () {
            this.value(this.initialValue);
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Boolean} True, if element is invalid.
         */
        validate: function () {
            var value = this.value(),
                msg = validator(this.validation, value),
                isValid = !this.visible() || !msg;

            this.error(msg);

            return {
                valid: isValid,
                target: this
            };
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this.trigger('update', this.hasChanged());

            this.validate();
        }
    });
});
