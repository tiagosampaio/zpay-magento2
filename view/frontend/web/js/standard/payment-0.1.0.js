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
    'qrCode',
    'timer',
    'moment',
    'mage/translate'
], function ($, qrCode, timer, moment) {
    return ZPay = {
        magentoAmount:null,
        QRCodeElement:null,
        updateButton:null,
        urlUpdate:null,
        urlVerify:null,
        urlSuccess:null,
        updateCallback:null,
        timer:null,
        timerContainer:null,
        orderUpdateInterval:10000,
        ZOrder:{
            orderId:null,
            quoteId:null,
            address:null,
            amount:null,
            time:null,
            timestamp:null
        },
        setup:function (QRCodeElement, updateButton, updateUrl, verifyUrl, verifiedUrl, orderUpdateInterval, updateCallback) {
            this.QRCodeElement = QRCodeElement;
            this.updateButton = updateButton;
            this.urlUpdate = updateUrl;
            this.urlVerify = verifyUrl;
            this.urlSuccess = verifiedUrl;
            this.updateCallback = updateCallback;
            this.orderUpdateInterval = orderUpdateInterval;

            return this;
        },
        setupZOrder: function (orderId, quoteId, address, amount, time, timestamp) {
            this.ZOrder.orderId   = orderId;
            this.ZOrder.quoteId   = quoteId;
            this.ZOrder.address   = address;
            this.ZOrder.amount    = amount;
            this.ZOrder.time      = time;
            this.ZOrder.timestamp = timestamp;

            return this;
        },
        start: function () {
            $(this.updateButton).on('click', $.proxy(this.updateQuote, this));

            this.startTimer();
            this.scheduleVerifyPayment();
            this.generateQRCode();

            return this;
        },
        generateQRCode: function () {
            qrCode.draw(this.QRCodeElement, this.generateQRCodeText());
            return this;
        },
        generateQRCodeText: function () {
            return "bitcoin:" + this.ZOrder.address + "?amount=" + parseFloat(this.ZOrder.amount);
        },
        schedule: function (callback, time) {
            setInterval(callback.bind(this), time);
            return this;
        },
        scheduleOnce: function (callback, time) {
            setTimeout(callback.bind(this), time);
            return this;
        },
        scheduleVerifyPayment: function () {
            this.scheduleOnce(this.verifyPayment, this.orderUpdateInterval);
            return this;
        },
        verifyPayment: function () {
            var object = this;

            $.ajax(object.urlVerify, {
                method:'GET',
                data:{order:object.ZOrder.orderId},
                dataType:'json',
                success: function (data) {
                    if (!data || !data.status) {
                        return;
                    }

                    if (data.status.toUpperCase() == 'PAID') {
                        window.location = object.urlSuccess;
                        return this;
                    }
                },
                error: function (data) {
                    // console.log('updateQuote Error', data);
                },
                complete: function (data) {
                    object.scheduleVerifyPayment();
                    // console.log('updateQuote Complete', data);
                }
            });

            return this;
        },
        updateQuote: function () {
            $(this.updateButton).text($.mage.__('Please wait while the quote is updated...'));

            $.ajax(this.urlUpdate, {
                method:'GET',
                data:{order:this.ZOrder.orderId},
                dataType:'json',
                success: this.updateQuoteSuccess.bind(this),
                error: function (data) {
                    // console.log('updateQuote Error', data);
                },
                complete: this.updateQuoteComplete.bind(this)
            });

            return this;
        },
        updateQuoteSuccess: function (data) {
            if (!data) {
                return this;
            }

            this.ZOrder.orderId   = data.order_id;
            this.ZOrder.quoteId   = data.quote_id;
            this.ZOrder.amount    = data.amount_to;
            this.ZOrder.address   = data.address;
            this.ZOrder.time      = parseInt(data.time);
            this.ZOrder.timestamp = data.timestamp;

            $('table.values .code-text').html(this.ZOrder.address);
            $('table.values .btc').html(this.ZOrder.amount);
            $('table.values .brl .price').html(data.total_brl);
            $('table.values .rate .price').html(data.rate);

            this.generateQRCode();

            if (this.updateCallback) {
                this.updateCallback(this);
            }

            this.restartTimer();
        },
        updateQuoteComplete: function (data) {
            $(this.updateButton).text($.mage.__('Update Quote'));
        },
        startTimer: function () {
            timer.start(this.ZOrder.timestamp);
            timer.onFinish = this.updateQuote.bind(this);
            return this;
        },
        stopTimer: function () {
            timer.stop();
            return this;
        },
        restartTimer: function () {
            this.stopTimer();
            this.startTimer();
            return this;
        },
    };
});
