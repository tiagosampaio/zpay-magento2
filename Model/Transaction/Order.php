<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\Transaction;

use Magento\Framework\Model\AbstractModel;
use ZPay\Standard\Api\Data\TransactionOrderInterface;
use ZPay\Standard\Model\ResourceModel\Transaction\Order as ResourceOrder;

/**
 * Class Order
 *
 * @package ZPay\Standard\Model\Transaction
 */
class Order extends AbstractModel implements TransactionOrderInterface
{
    protected function _construct()
    {
        $this->_init(ResourceOrder::class);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @param int $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getZpayOrderId()
    {
        return $this->getData(self::ZPAY_ORDER_ID);
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setZpayOrderId($orderId)
    {
        return $this->setData(self::ZPAY_ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getZpayQuoteId()
    {
        return $this->getData(self::ZPAY_QUOTE_ID);
    }

    /**
     * @param string $quoteId
     *
     * @return $this
     */
    public function setZpayQuoteId($quoteId)
    {
        return $this->setData(self::ZPAY_QUOTE_ID, $quoteId);
    }

    /**
     * @return string
     */
    public function getZpayAddress()
    {
        return $this->getData(self::ZPAY_ADDRESS);
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setZpayAddress($address)
    {
        return $this->setData(self::ZPAY_ADDRESS, $address);
    }

    /**
     * @return string
     */
    public function getZpayOrderStatus()
    {
        return $this->getData(self::ZPAY_ORDER_STATUS);
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setZpayOrderStatus($status)
    {
        return $this->setData(self::ZPAY_ORDER_STATUS, $status);
    }

    /**
     * @return string
     */
    public function getZpayPayoutStatus()
    {
        return $this->getData(self::ZPAY_PAYOUT_STATUS);
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setZpayPayoutStatus($status)
    {
        return $this->setData(self::ZPAY_PAYOUT_STATUS, $status);
    }

    /**
     * @return float
     */
    public function getZpayAmountTo()
    {
        return $this->getData(self::ZPAY_AMOUNT_TO);
    }

    /**
     * @param float $amount
     *
     * @return $this
     */
    public function setZpayAmountTo($amount)
    {
        return $this->setData(self::ZPAY_AMOUNT_TO, $amount);
    }

    /**
     * @return integer
     */
    public function getZpayTime()
    {
        return $this->getData(self::ZPAY_TIME);
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setZpayTime($time)
    {
        return $this->setData(self::ZPAY_TIME, $time);
    }

    /**
     * @return string
     */
    public function getZpayTimestamp()
    {
        return $this->getData(self::ZPAY_TIMESTAMP);
    }

    /**
     * @param string $timestamp
     *
     * @return $this
     */
    public function setZpayTimestamp($timestamp)
    {
        return $this->setData(self::ZPAY_TIMESTAMP, $timestamp);
    }
}
