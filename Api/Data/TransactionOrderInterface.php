<?php

namespace ZPay\Standard\Api\Data;

/**
 * Interface TransactionOrderInterface
 *
 * @method integer getId()
 * @method integer getQuoteId()
 * @method integer getOrderId()
 * @method string  getZpayOrderId()
 * @method string  getZpayQuoteId()
 * @method string  getZpayAddress()
 * @method string  getZpayOrderStatus()
 * @method string  getZpayPayoutStatus()
 * @method float   getZpayAmountTo()
 * @method integer getZpayTime()
 * @method string  getZpayTimestamp()
 *
 * @method $this setId(integer $id)
 * @method $this setQuoteId(integer $quoteId)
 * @method $this setOrderId(integer $orderId)
 * @method $this setZpayOrderId(integer $zpayOrderId)
 * @method $this setZpayQuoteId(integer $zpayQuoteId)
 * @method $this setZpayAddress(string $zpayAddress)
 * @method $this setZpayOrderStatus(string $status)
 * @method $this setZpayPayoutStatus(string $status)
 * @method $this setZpayAmountTo(float $zpayAmountTo)
 * @method $this setZpayTime(integer $zpayTime)
 * @method $this setZpayTimestamp(string $zpayTimestamp)
 *
 * @package ZPay\Standard\Api\Data
 */
interface TransactionOrderInterface
{
}
