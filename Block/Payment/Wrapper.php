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

namespace ZPay\Standard\Block\Payment;

use Magento\Framework\View\Element\Template;

/**
 * Class Wrapper
 *
 * @package ZPay\Standard\Block\Payment
 */
class Wrapper extends Template
{
    /**
     * @var string
     */
    protected $_template = 'ZPay_Standard::payment/wrapper.phtml';

    /**
     *
     * @var null|\ZPay\Standard\Helper\Data
     */
    private $helperData = null;

    /**
     * @var null|\Magento\Framework\Pricing\Helper\Data
     */
    private $helperPricing = null;

    /**
     * @var \Magento\Framework\Session\Storage
     */
    private $storage = null;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface
     */
    private $transactionOrderRepository;

    /**
     * @var \ZPay\Standard\Api\TransactionStatusVerification
     */
    private $statusVerification;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \ZPay\Standard\Model\TimeCalculator
     */
    private $timeCalculator;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $helperPricing,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \ZPay\Standard\Api\TransactionStatusVerification $statusVerification,
        \ZPay\Standard\Model\TimeCalculator $timeCalculator,
        \ZPay\Standard\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helperData = $helperData;
        $this->helperPricing = $helperPricing;
        $this->storage = $storage;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->statusVerification = $statusVerification;
        $this->timezone = $timezone;
        $this->timeCalculator = $timeCalculator;
    }

    /**
     * @return \ZPay\Standard\Model\Transaction\Order
     */
    public function getZpayOrder()
    {
        $key = 'zpay_order';

        if ($this->registry->registry($key)) {
            return $this->registry->registry($key);
        }

        try {
            /** After verified action */
            $orderId = (int) $this->storage->getData('current_order_id');

            /** Let's make available only one time. */
            if ($orderId) {
                $this->storage->unsetData('current_order_id');
            }

            /** Checkout success page. */
            if (!$orderId) {
                $orderId = $this->getOrder()->getId();
            }

            /** @var \ZPay\Standard\Model\Transaction\Order $order */
            $order = $this->transactionOrderRepository->getByOrderId($orderId);

            $this->registry->register($key, $order);
        } catch (\Exception $e) {
        }

        return $order;
    }

    /**
     * @return bool
     */
    public function canDisplayWrapper()
    {
        $zpayOrderId = $this->getZpayOrder()->getId();

        return (bool) $zpayOrderId;
    }

    /**
     * @return \Magento\Sales\Model\Order
     *
     * @throws \Exception
     */
    public function getOrder()
    {
        $key = 'current_magento_order';

        if ($this->registry->registry($key)) {
            return $this->registry->registry($key);
        }

        if ($orderId = $this->getRequest()->getParam('order_id')) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);

            $this->registry->register($key, $order, true);

            return $order;
        }

        /** @var \Magento\Sales\Model\Order $lastOrder */
        $lastOrder = $this->checkoutSession->getLastRealOrder();

        return $lastOrder;
    }

    /**
     * @return \ZPay\Standard\Helper\Data
     */
    public function dataHelper()
    {
        return $this->helperData;
    }

    /**
     * @return string
     */
    public function getPaymentAddress()
    {
        return (string) $this->storage->getData('address');
    }

    /**
     * @param float $value
     * @param bool  $format
     * @param bool  $includeContainer
     *
     * @return float|string
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $this->helperPricing->currency($value, $format, $includeContainer);
    }

    /**
     * @return float|int
     */
    public function getBtcRate()
    {
        try {
            $grandTotal = (float) $this->getOrder()->getGrandTotal();
            $amountTo = (float) $this->getZpayOrder()->getZpayAmountTo();

            return (float) ($grandTotal / $amountTo);
        } catch (\Exception $e) {
        }

        return 0;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param      $timestamp
     * @param null $format
     *
     * @return string
     */
    public function convertDate($timestamp, $format = null)
    {
        return $this->timezone
            ->date($timestamp, $this->getTimezone()->getDefaultTimezonePath())
            ->format($format ? : \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @param string $timestamp
     * @param int    $milliseconds
     * @return string
     * @throws \Exception
     */
    public function calculateTimestamp($timestamp, $milliseconds)
    {
        $timestamp = $this->timeCalculator->calculate($timestamp, $milliseconds);
        return $timestamp->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->statusVerification->isPaid($this->getZpayOrder())) {
            return null;
        }

        return parent::_toHtml();
    }
}
