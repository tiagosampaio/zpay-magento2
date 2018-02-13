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
    }
};
