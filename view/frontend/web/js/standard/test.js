/*
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package ZPay\Standard
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
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
