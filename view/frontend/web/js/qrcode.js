/*
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */
define(['jquery', 'jquery-qrcode'], function($) {
    'use strict';

    var options = {
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
        text: 'Sample text.',

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
    };

    return {
        defaults: options,
        draw: function (element, text) {
            this.defaults.text = text;
            $(element).qrcode(this.defaults);
            return this;
        }
    };
});
