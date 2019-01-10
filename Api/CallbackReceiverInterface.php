<?php
/**
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

declare(strict_types = 1);

namespace ZPay\Standard\Api;

/**
 * Class CallbackReceiverInterface
 *
 * @package ZPay\Standard\Api
 * @api
 */
interface CallbackReceiverInterface
{
    /**
     * @param string $orderId
     *
     * @throws \ZPay\Standard\Exception\LocalizedException
     * @throws \Exception
     *
     * @return mixed
     */
    public function process($orderId);
}
