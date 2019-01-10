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
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'zpay_standard',
        component: 'ZPay_Standard/js/view/payment/method-renderer/zpay-standard-method'
    });

    /** Add view logic here if needed */
    return Component.extend({
        // afterPlaceOrder: function () {
        //     window.location = '/zpay/standard/redirect';
        // }
    });
});
