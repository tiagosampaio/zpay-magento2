<?php

namespace ZPay\Standard\Api;

use \ZPay\Standard\Api\Data\TransactionOrderInterface;

interface TransactionVerification
{

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $paymentStatus
     * @return boolean
     */
    public function isPaid(TransactionOrderInterface $transactionOrder, $paymentStatus);

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isCompleted(TransactionOrderInterface $transactionOrder, $orderStatus);
}
