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
 * Class CallbackProcessorInterface
 *
 * @package ZPay\Standard\Api
 */
interface CallbackProcessorInterface
{
    /**
     * @var int
     */
    const RESULT_CODE_ERROR = 400;

    /**
     * @var int
     */
    const RESULT_NOT_FOUND = 404;

    /**
     * @var int
     */
    const RESULT_PAYMENT_REQUIRED = 402;

    /**
     * @var int
     */
    const RESULT_PROCESSING = 102;

    /**
     * @var int
     */
    const RESULT_CODE_SUCCESS = 200;

    /**
     * @param string $transactionOrder
     *
     * @throws \ZPay\Standard\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @return mixed
     */
    public function processCallback($zPayOrderId);
}
