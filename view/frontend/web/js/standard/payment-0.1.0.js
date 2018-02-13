define(['jquery', 'jquery-qrcode'], function (jQuery) {
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
            jQuery(this.updateButton).on('click', jQuery.proxy(this.updateQuote, this));

            this.startTimer();
            this.scheduleUpdateQuote();
            this.scheduleVerifyPayment();
            this.generateQRCode();

            return this;
        },
        generateQRCode: function () {
            jQuery(this.QRCodeElement).qrcode({
                // render method: 'canvas', 'image' or 'div'
                render: 'canvas',

                // version range somewhere in 1 .. 40
                minVersion: 10,
                maxVersion: 40,

                // error correction level: 'L', 'M', 'Q' or 'H'
                ecLevel: 'L',

                // offset in pixel if drawn onto existing canvas
                left: 0,
                top: 0,

                // size in pixel
                size: 300,

                // code color or image element
                fill: '#333333',

                // background color or image element, null for transparent background
                background: '#fff',

                // content
                text: "bitcoin:" + this.ZOrder.address + "?amount=" + parseFloat(this.ZOrder.amount),

                // corner radius relative to module width: 0.0 .. 0.5
                radius: 0.3,

                // quiet zone in modules
                quiet: 2,

                // modes
                // 0: normal
                // 1: label strip
                // 2: label box
                // 3: image strip
                // 4: image box
                mode: 2,

                mSize: 0.1,
                mPosX: 0.5,
                mPosY: 0.5,

                label: 'ZPay',
                fontname: '"Raleway", "Helvetica Neue", Verdana, Arial, sans-serif',
                fontcolor: '#ff9818',

                image: null
            });

            return this;
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

            jQuery.ajax(object.urlVerify, {
                method:'GET',
                data:{order:object.ZOrder.orderId},
                dataType:'json',
                success: function (data) {
                    if (!data.status) {
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
            var object = this;

            jQuery.ajax(object.urlUpdate, {
                method:'GET',
                data:{order:object.ZOrder.orderId},
                dataType:'json',
                success: function (data) {
                    object.ZOrder.orderId   = data.order_id;
                    object.ZOrder.quoteId   = data.quote_id;
                    object.ZOrder.amount    = data.amount_to;
                    object.ZOrder.address   = data.address;
                    object.ZOrder.time      = data.time;
                    object.ZOrder.timestamp = data.timestamp;

                    object.generateQRCode();

                    if (object.updateCallback) {
                        object.updateCallback(object);
                    }

                    object.restartTimer();
                    // console.log('success', data);
                },
                error: function (data) {
                    // console.log('updateQuote Error', data);
                },
                complete: function (data) {
                    // console.log('updateQuote Complete', data);
                }
            });

            return this;
        },
        startTimer: function () {
            var countDownDate = new Date(this.ZOrder.timestamp).getTime();

            // Update the count down every 1 second
            this.timer = setInterval(function(object) {
                // Get todays date and time
                var now = new Date().getTime();

                // Find the distance between now an the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days    = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours   = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (distance >= 0) {
                    object.updateTimer(days, hours, minutes, seconds);
                    return;
                }

                // If the count down is finished, write some text
                if (distance < 0) {
                    // object.stopTimer()
                    //     .updateQuote();
                }
            }, 1000, this);

            return this;
        },
        stopTimer: function () {
            // clearInterval(this.timer);
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
            jQuery(this.timerContainer).text(text);
            // console.log(text);

            return this;
        },
        setTimerContainer: function (container) {
            this.timerContainer = container;
            return this;
        }
    };
});
