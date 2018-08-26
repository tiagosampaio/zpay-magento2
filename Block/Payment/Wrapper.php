<?php

namespace ZPay\Standard\Block\Payment;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\StorageInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ZPay\Standard\Helper\Data as HelperData;
use Magento\Framework\Pricing\Helper\Data as HelperPricing;
use ZPay\Standard\Api\TransactionStatusVerification;

class Wrapper extends Template
{

    /** @var string */
    protected $_template = 'ZPay_Standard::payment/wrapper.phtml';

    /** @var ObjectManagerInterface */
    protected $objectManager = null;

    /** @var null|HelperData */
    protected $helperData = null;

    /** @var null|HelperPricing */
    protected $helperPricing = null;

    /** @var \Magento\Framework\Session\Storage */
    protected $storage = null;

    /** @var Registry */
    protected $registry = null;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface */
    protected $transactionOrderRepository;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $timezone;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        HelperData $helperData,
        HelperPricing $helperPricing,
        StorageInterface $storage,
        Registry $registry,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->helperData = $helperData;
        $this->helperPricing = $helperPricing;
        $this->objectManager = $objectManager;
        $this->storage = $storage;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->timezone = $timezone;
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
     * @return HelperData
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
     * @return string
     */
    public function convertDate($timestamp, $format = null)
    {
        return $this->timezone
            ->date($timestamp, $this->getTimezone()->getDefaultTimezonePath())
            ->format($format ?: \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getZpayOrder()->getZpayPayoutStatus() == TransactionStatusVerification::PAYMENT_STATUS_PAID) {
            return null;
        }

        return parent::_toHtml();
    }
}
