<?php

namespace ZPay\Standard\Block\Payment;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\StorageInterface;
use ZPay\Standard\Model\Transaction\Order as ZPayOrder;
use ZPay\Standard\Helper\Data as HelperData;
use Magento\Framework\Pricing\Helper\Data as HelperPricing;

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


    /**
     * Wrapper constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param HelperData             $helperData
     * @param HelperPricing          $helperPricing
     * @param StorageInterface       $storage
     * @param Registry               $registry
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        HelperData $helperData,
        HelperPricing $helperPricing,
        StorageInterface $storage,
        Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->helperData    = $helperData;
        $this->helperPricing = $helperPricing;
        $this->objectManager = $objectManager;
        $this->storage       = $storage;
        $this->registry      = $registry;
    }


    /**
     * @return ZPayOrder
     */
    public function getZpayOrder()
    {
        $key = 'zpay_order';

        if ($this->registry->registry($key)) {
            return $this->registry->registry($key);
        }

        /** @var ZPayOrder $order */
        $order = $this->objectManager->create(ZPayOrder::class);

        try {
            /** After verified action */
            $orderId = (int) $this->storage->getData('current_order_id');

            /** Let's make available only one time. */
            if ($orderId) {
                $this->storage->unsetData('current_order_id');
            }

            /** Checkout success page. */
            if (!$orderId) {
                $orderId = $this->getLastOrder()->getId();
            }

            if ($orderId) {
                $order->load($orderId, 'order_id');
            }

            $this->registry->register($key, $order);
        } catch (\Exception $e) {

        }

        return $order;
    }


    /**
     * @return \Magento\Sales\Model\Order
     *
     * @throws \Exception
     */
    public function getLastOrder()
    {
        // $this->getCheckoutSession()->setLastRealOrderId('000000052');

        /** @var \Magento\Sales\Model\Order $lastOrder */
        $lastOrder = $this->getCheckoutSession()->getLastRealOrder();

        return $lastOrder;
    }


    /**
     * @return CheckoutSession
     */
    public function getCheckoutSession()
    {
        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        return $checkoutSession;
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
            $grandTotal = (float) $this->getLastOrder()->getGrandTotal();
            $amountTo   = (float) $this->getZpayOrder()->getZpayAmountTo();

            return  (float) ($grandTotal/$amountTo);
        } catch (\Exception $e) {

        }

        return 0;
    }

}
