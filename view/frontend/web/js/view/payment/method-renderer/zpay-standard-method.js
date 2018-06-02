/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default'
], function ($, Component) {
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
