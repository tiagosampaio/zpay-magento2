/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('zpay.test', {
        options: {
        },

        /** @inheritdoc */
        _create: function () {
            console.log('Test ZPay is initialized.');
        }
    });

    return $.zpay.test;
});
