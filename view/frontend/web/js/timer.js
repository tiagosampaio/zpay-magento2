/*
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

define(['jquery', 'countdown'], function ($) {
    return {
        defaults: {
            container: $('.countdown'),
        },
        container: function (container) {
            if (container) {
                this.defaults.container = container;
                return this;
            }

            return $(this.defaults.container);
        },
        start: function (timestamp, container) {
            this.container(container);
            this.container()
                .countdown(timestamp, this.updateContainer.bind(this))
                .on('update.countdown', this.onUpdate.bind(this))
                .on('finish.countdown', this.onFinish.bind(this))
                .on('stop.countdown', this.onStop.bind(this));

            return this;
        },
        stop: function () {
            this.container().countdown('stop');
            return this;
        },
        pause: function () {
            this.container().countdown('pause');
            return this;
        },
        resume: function () {
            this.container().countdown('resume');
            return this;
        },
        restart: function (timestamp, element) {
            console.log(this);
            this.container(element);
            console.log(this);
            this.stop();
            console.log(this);
            this.start(timestamp);
            console.log(this);
            return this;
        },
        onUpdate: function () {
        },
        onFinish: function () {
        },
        onStop: function () {
        },
        updateContainer: function (event) {
            this.container().text(
                event.strftime('%H:%M:%S')
            );

            return this;
        }
    };
});
