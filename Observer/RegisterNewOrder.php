<?php

namespace ZPay\Standard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class RegisterNewOrder implements ObserverInterface
{

    /** @var \Magento\Framework\Registry */
    protected $_registry;

    /** @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface */
    private $orderTransactionRepository;

    /**
     * RegisterNewOrder constructor.
     *
     * @param \Magento\Framework\Registry                            $registry
     * @param \ZPay\Standard\Api\TransactionOrderRepositoryInterface $orderTransactionRepository
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $orderTransactionRepository
    ) {
        $this->_registry = $registry;
        $this->orderTransactionRepository = $orderTransactionRepository;
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
        /** @var Order $order */
        $order = $observer->getData('order');

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
         * @var \ZPay\Standard\Model\Transaction\Order               $orderTransaction
         * @var \ZPay\Standard\Model\ResourceModel\Transaction\Order $zResourceOrder
         */
        $orderTransaction = $this->orderTransactionRepository->getByOrderId($salesOrder->getId());

        $orderTransaction->setOrderId($salesOrder->getId())
            ->setQuoteId($salesOrder->getQuoteId())
            ->setZpayOrderId($data->order_id)
            ->setZpayQuoteId($data->quote_id)
            ->setZpayAddress($data->address)
            ->setZpayAmountTo((float) $data->amount_to)
            ->setZpayTime($data->time)
            ->setZpayTimestamp(date('Y-m-d H:i:s', strtotime($data->timestamp)));

        $this->orderTransactionRepository->save($orderTransaction);

        return $orderTransaction;
    }
}
