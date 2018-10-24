<?php

namespace ZPay\Standard\Model\Transaction;

use ZPay\Standard\Api\Data\TransactionOrderInterface;

class StatusVerification implements \ZPay\Standard\Api\TransactionStatusVerification
{

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isPaid($paymentStatus)
    {
        return $this->compareStatus($this->getPaymentStatus($paymentStatus), self::PAYMENT_STATUS_PAID);
    }

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isUnpaid($paymentStatus)
    {
        return $this->compareStatus($this->getPaymentStatus($paymentStatus), self::PAYMENT_STATUS_UNPAID);
    }

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isOverpaid($paymentStatus)
    {
        return $this->compareStatus($this->getPaymentStatus($paymentStatus), self::PAYMENT_STATUS_OVERPAID);
    }

    /**
     * @param TransactionOrderInterface|string $paymentStatus
     * @return boolean
     */
    public function isUnderpaid($paymentStatus)
    {
        return $this->compareStatus($this->getPaymentStatus($paymentStatus), self::PAYMENT_STATUS_UNDERPAID);
    }

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCompleted($orderStatus)
    {
        return $this->compareStatus($this->getOrderStatus($orderStatus), self::ORDER_STATUS_COMPLETED);
    }

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCreated($orderStatus)
    {
        return $this->compareStatus($this->getOrderStatus($orderStatus), self::ORDER_STATUS_CREATED);
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     * @param                           $orderStatus
     * @return boolean
     */
    public function isFailed($orderStatus)
    {
        return $this->compareStatus($this->getOrderStatus($orderStatus), self::ORDER_STATUS_FAILED);
    }

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isProcessing($orderStatus)
    {
        return $this->compareStatus($this->getOrderStatus($orderStatus), self::ORDER_STATUS_PROCESSING);
    }

    /**
     * @param TransactionOrderInterface|string $orderStatus
     * @return boolean
     */
    public function isCanceled($orderStatus)
    {
        if (self::ORDER_STATUS_CANCELED == $this->getOrderStatus($orderStatus)) {
            return true;
        }

        return false;
    }
    
    /**
     * @param string $status
     * @param string $comparedStatus
     *
     * @return bool
     */
    private function compareStatus($status, $comparedStatus)
    {
        return $status === $comparedStatus;
    }
    
    /**
     * @param TransactionOrderInterface|string $object
     *
     * @return string
     */
    private function getPaymentStatus($object)
    {
        if ($object instanceof TransactionOrderInterface) {
            return $object->getZpayPayoutStatus();
        }
        
        return (string) $object;
    }
    
    /**
     * @param TransactionOrderInterface|string $object
     *
     * @return string
     */
    private function getOrderStatus($object)
    {
        if ($object instanceof TransactionOrderInterface) {
            return $object->getZpayOrderStatus();
        }
        
        return (string) $object;
    }
}
