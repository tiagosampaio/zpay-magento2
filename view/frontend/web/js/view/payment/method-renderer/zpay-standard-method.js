/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
define([
    'Magento_Checkout/js/view/payment/default'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ZPay_Standard/payment/standard'
        },
        // afterPlaceOrder: function () {
        //     window.location = '/zpay/standard/redirect';
        // }
    });
});
