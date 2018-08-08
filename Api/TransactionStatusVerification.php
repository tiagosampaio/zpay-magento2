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
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isPaid(TransactionOrderInterface $transactionOrder, $paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isUnpaid(TransactionOrderInterface $transactionOrder, $paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isOverpaid(TransactionOrderInterface $transactionOrder, $paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isUnderpaid(TransactionOrderInterface $transactionOrder, $paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCompleted(TransactionOrderInterface $transactionOrder, $orderStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCreated(TransactionOrderInterface $transactionOrder, $orderStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isFailed(TransactionOrderInterface $transactionOrder, $orderStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isProcessing(TransactionOrderInterface $transactionOrder, $orderStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCanceled(TransactionOrderInterface $transactionOrder, $orderStatus);
}
