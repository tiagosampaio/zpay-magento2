<?php

namespace ZPay\Standard\Model\Transaction;

use ZPay\Standard\Api\Data\TransactionOrderInterface;

class Verification implements \ZPay\Standard\Api\TransactionVerification
{

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isPaid(TransactionOrderInterface $transactionOrder, $paymentStatus)
    {
        if (\ZPay\Standard\Controller\Payment\PaymentAbstract::PAYMENT_STATUS_PAID == $paymentStatus) {
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
        if (\ZPay\Standard\Controller\Payment\PaymentAbstract::ORDER_STATUS_COMPLETED == $orderStatus) {
            return true;
        }

        return false;
    }
}
