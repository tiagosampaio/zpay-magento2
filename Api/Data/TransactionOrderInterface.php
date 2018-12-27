<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Api\Data;

/**
 * Interface TransactionOrderInterface
 *
 * @package ZPay\Standard\Api\Data
 */
interface TransactionOrderInterface
{
    /**
     * @var string
     */
    const ID = 'id';
    
    /**
     * @var string
     */
    const QUOTE_ID = 'quote_id';
    
    /**
     * @var string
     */
    const ORDER_ID = 'order_id';
    
    /**
     * @var string
     */
    const ZPAY_ORDER_ID = 'zpay_order_id';
    
    /**
     * @var string
     */
    const ZPAY_QUOTE_ID = 'zpay_quote_id';
    
    /**
     * @var string
     */
    const ZPAY_ADDRESS = 'zpay_address';
    
    /**
     * @var string
     */
    const ZPAY_ORDER_STATUS = 'zpay_order_status';
    
    /**
     * @var string
     */
    const ZPAY_PAYOUT_STATUS = 'zpay_payout_status';
    
    /**
     * @var string
     */
    const ZPAY_AMOUNT_TO = 'zpay_amount_to';
    
    /**
     * @var string
     */
    const ZPAY_TIME = 'zpay_time';
    
    /**
     * @var string
     */
    const ZPAY_TIMESTAMP = 'zpay_timestamp';
    
    /**
     * @return int
     */
    public function getQuoteId();
    
    /**
     * @param int $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId);
    
    /**
     * @return int
     */
    public function getOrderId();
    
    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);
    
    /**
     * @return string
     */
    public function getZpayOrderId();
    
    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setZpayOrderId($orderId);
    
    /**
     * @return string
     */
    public function getZpayQuoteId();
    
    /**
     * @param string $quoteId
     *
     * @return $this
     */
    public function setZpayQuoteId($quoteId);
    
    /**
     * @return string
     */
    public function getZpayAddress();
    
    /**
     * @param string $address
     *
     * @return $this
     */
    public function setZpayAddress($address);
    
    /**
     * @return string
     */
    public function getZpayOrderStatus();
    
    /**
     * @param string $status
     *
     * @return $this
     */
    public function setZpayOrderStatus($status);
    
    /**
     * @return string
     */
    public function getZpayPayoutStatus();
    
    /**
     * @param string $status
     *
     * @return $this
     */
    public function setZpayPayoutStatus($status);
    
    /**
     * @return float
     */
    public function getZpayAmountTo();
    
    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setZpayAmountTo($amount);
    
    /**
     * @return integer
     */
    public function getZpayTime();
    
    /**
     * @param int $time
     *
     * @return $this
     */
    public function setZpayTime($time);
    
    /**
     * @return string
     */
    public function getZpayTimestamp();
    
    /**
     * @param string $timestamp
     *
     * @return $this
     */
    public function setZpayTimestamp($timestamp);
}
