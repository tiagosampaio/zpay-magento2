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
    'moment',
    'mage/translate'
], function ($, qrCode, moment) {
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
            this.scheduleUpdateQuote();
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
        scheduleUpdateQuote: function () {
            var time = parseInt(this.ZOrder.time)*100;
            // this.schedule(this.updateQuote, time);

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
            let limitTime = moment(this.ZOrder.timestamp).add(this.ZOrder.time, 'milliseconds');
            let now       = moment();

            let duration  = moment.duration(limitTime.diff(now));

            // Update the count down every 1 second
            this.timer = setInterval(function(object) {
                duration.subtract(moment.duration(1, 'seconds'));

                if (duration.asMilliseconds() > 0) {
                    object.updateTimer(duration.days(), duration.hours(), duration.minutes(), duration.seconds());
                    return;
                }

                // If the count down is finished, write some text
                if (duration.asMilliseconds() <= 0) {
                    object.stopTimer()
                        .updateQuote();
                }
            }, 1000, this);

            return this;
        },
        stopTimer: function () {
            clearInterval(this.timer);
            return this;
        },
        restartTimer: function () {
            this.stopTimer();
            this.startTimer();
            return this;
        },
        updateTimer: function (days, hours, minutes, seconds) {
            if (hours<10)   {hours   = "0"+hours}
            if (minutes<10) {minutes = "0"+minutes}
            if (seconds<10) {seconds = "0"+seconds}

            var text = hours + ":" + minutes + ":" + seconds;
            $(this.timerContainer).text(text);
            // console.log(text);

            return this;
        },
        setTimerContainer: function (container) {
            this.timerContainer = container;
            return this;
        }
    };
});
