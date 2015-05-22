/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mageUtils',
    './abstract'
], function (moment, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            dateFormat: 'MM/DD/YYYY',
            options: {}
        },

        initProperties: function () {
            this.dateFormat = utils.normalizeDate(this.dateFormat);

            return this._super();
        },

        /**
         * Converts initial value to the specified date format.
         *
         * @returns {String}
         */
        getInititalValue: function () {
            var value = this._super();

            if (value) {
                value = moment(value).format(this.dateFormat);
            }

            return value;
        }
    });
});
