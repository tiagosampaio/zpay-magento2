<?php

namespace ZPay\Standard\Model\Transaction;

use ZPay\Standard\Api\Data\TransactionOrderInterface;

class StatusVerification implements \ZPay\Standard\Api\TransactionStatusVerification
{

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isPaid(TransactionOrderInterface $transactionOrder, $paymentStatus)
    {
        if (self::PAYMENT_STATUS_PAID == $paymentStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isUnpaid(TransactionOrderInterface $transactionOrder, $paymentStatus)
    {
        if (self::PAYMENT_STATUS_UNPAID == $paymentStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isOverpaid(TransactionOrderInterface $transactionOrder, $paymentStatus)
    {
        if (self::PAYMENT_STATUS_OVERPAID == $paymentStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isUnderpaid(TransactionOrderInterface $transactionOrder, $paymentStatus)
    {
        if (self::PAYMENT_STATUS_UNDERPAID == $paymentStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCompleted(TransactionOrderInterface $transactionOrder, $orderStatus)
    {
        if (self::ORDER_STATUS_COMPLETED == $orderStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCreated(TransactionOrderInterface $transactionOrder, $orderStatus)
    {
        if (self::ORDER_STATUS_CREATED == $orderStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isFailed(TransactionOrderInterface $transactionOrder, $orderStatus)
    {
        if (self::ORDER_STATUS_FAILED == $orderStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isProcessing(TransactionOrderInterface $transactionOrder, $orderStatus)
    {
        if (self::ORDER_STATUS_PROCESSING == $orderStatus) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCanceled(TransactionOrderInterface $transactionOrder, $orderStatus)
    {
        if (self::ORDER_STATUS_CANCELED == $orderStatus) {
            return true;
        }

        return false;
    }
}
