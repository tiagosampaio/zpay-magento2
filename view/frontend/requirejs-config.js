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

var config = {
    paths: {
        'jquery-qrcode':'ZPay_Standard/js/lib/jquery-qrcode.min',
        'zpay-payment':'ZPay_Standard/js/standard/payment-0.1.0'
    },
    shim:{
        'jquery-qrcode':{
            'deps':['jquery']
        },
        'zpay-payment':{
            'deps':['jquery-qrcode']
        }
    },
    map: {
        '*': {
            qrCode: 'ZPay_Standard/js/qrcode'
        }
    }
};
