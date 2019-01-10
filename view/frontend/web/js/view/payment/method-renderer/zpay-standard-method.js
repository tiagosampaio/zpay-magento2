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
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: false,
        defaults: {
            template: 'ZPay_Standard/payment/standard'
        },
        afterPlaceOrder: function () {
            $.mage.redirect('/zpay/payment');
            return false;
        }
    });
});
