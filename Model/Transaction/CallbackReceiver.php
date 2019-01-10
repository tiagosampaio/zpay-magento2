<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package  ZPay\Standard
 * @author   Tiago Sampaio <tiago@tiagosampaio.com>
 * @link     https://github.com/tiagosampaio
 * @link     https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Model\Transaction;

use ZPay\Standard\Api\CallbackProcessorInterface;
use ZPay\Standard\Api\CallbackReceiverInterface;
use ZPay\Standard\Exception\LocalizedException;

/**
 * Class CallbackReceiver
 *
 * @package ZPay\Standard\Model\Transaction
 * @api
 */
class CallbackReceiver implements CallbackReceiverInterface
{
    /**
     * @var CallbackProcessorInterface
     */
    protected $callbackProcessor;

    /**
     * CallbackReceiver constructor.
     *
     * @param CallbackProcessorInterface $callbackProcessor
     */
    public function __construct(
        CallbackProcessorInterface $callbackProcessor
    ) {
        $this->callbackProcessor = $callbackProcessor;
    }

    /**
     * @inheritdoc
     */
    public function process($orderId)
    {
        try {
            $this->callbackProcessor->processCallback($orderId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()), $e->getHttpCode());
        } catch (\Exception $e) {
            throw new \Exception(__('Some problem has occurred when trying to register a new invoice.'));
        }

        return ['result' => true];
    }
}
