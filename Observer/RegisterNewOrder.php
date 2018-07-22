<?php

namespace ZPay\Standard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class RegisterNewOrder implements ObserverInterface
{

    /** @var Registry */
    protected $_registry;

    /** @var ObjectManagerInterface */
    protected $_objectManager;


    /**
     * RegisterNewOrder constructor.
     *
     * @param Registry               $registry
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Registry $registry, ObjectManagerInterface $objectManager)
    {
        $this->_registry = $registry;
        $this->_objectManager = $objectManager;
    }


    /**
     * @param Observer $observer
     *
     * @return $this|void
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         * @var Quote $quote
         */
        $order = $observer->getData('order');
        // $quote = $observer->getData('quote');

        /** @var \stdClass $result */
        $result = $order->getData('zpay_api_result');

        if (!$result || !($result instanceof \stdClass)) {
            return;
        }

        /**
         * Is not a valid object.
         */
        if (!$result->order_id) {
            return;
        }

        /** @var \ZPay\Standard\Model\Transaction\Order $zOrder */
        $zOrder = $this->updateOrder($order, $result);
    }


    /**
     * @param Order     $salesOrder
     * @param \stdClass $data
     *
     * @return \ZPay\Standard\Model\Transaction\Order
     *
     * @throws \Exception
     */
    protected function updateOrder(Order $salesOrder, \stdClass $data)
    {
        /**
         * @var \ZPay\Standard\Model\Transaction\Order               $zOrder
         * @var \ZPay\Standard\Model\ResourceModel\Transaction\Order $zResourceOrder
         */
        $zOrder = $this->_objectManager->create(\ZPay\Standard\Model\Transaction\Order::class);
        // $zResourceOrder = $this->objectManager->create(\ZPay\Standard\Model\ResourceModel\Transaction\Order::class);

        /**
         * @todo Use Service Contracts instead of entity load.
         */
        $zOrder->load($salesOrder->getId(), 'order_id');

        $zOrder->setOrderId($salesOrder->getId())
            ->setQuoteId($salesOrder->getQuoteId())
            ->setZpayOrderId($data->order_id)
            ->setZpayQuoteId($data->quote_id)
            ->setZpayAddress($data->address)
            ->setZpayAmountTo((float) $data->amount_to)
            ->setZpayTime($data->time)
            ->setZpayTimestamp(date('Y-m-d H:i:s', strtotime($data->timestamp)));

        $zOrder->save();

        return $zOrder;
    }
}
