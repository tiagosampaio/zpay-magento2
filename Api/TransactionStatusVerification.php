<?php

namespace ZPay\Standard\Api;

use \ZPay\Standard\Api\Data\TransactionOrderInterface;

interface TransactionStatusVerification
{

    /** @var string */
    const PAYMENT_STATUS_PAID = 'PAID';

    /** @var string */
    const PAYMENT_STATUS_UNPAID = 'UNPAID';

    /** @var string */
    const PAYMENT_STATUS_OVERPAID = 'OVERPAID';

    /** @var string */
    const PAYMENT_STATUS_UNDERPAID = 'UNDERPAID';

    /** @var string */
    const ORDER_STATUS_CREATED = 'CREATED';

    /** @var string */
    const ORDER_STATUS_PROCESSING = 'PROCESSING';

    /** @var string */
    const ORDER_STATUS_FAILED = 'FAILED';

    /** @var string */
    const ORDER_STATUS_CANCELED = 'CANCELED';

    /** @var string */
    const ORDER_STATUS_COMPLETED = 'COMPLETED';

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isPaid($paymentStatus);

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isUnpaid($paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isOverpaid($paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isUnderpaid($paymentStatus);

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCompleted($orderStatus);

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCreated($orderStatus);

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isFailed($orderStatus);

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isProcessing($orderStatus);

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCanceled($orderStatus);
}
