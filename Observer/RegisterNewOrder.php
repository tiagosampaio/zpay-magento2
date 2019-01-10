<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package ZPay\Standard
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

/**
 * Class RegisterNewOrder
 *
 * @package ZPay\Standard\Observer
 */
class RegisterNewOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $_registry;

    /**
     * @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface
     */
    private $orderTransactionRepository;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * RegisterNewOrder constructor.
     *
     * @param \Magento\Framework\Registry                            $registry
     * @param \ZPay\Standard\Api\TransactionOrderRepositoryInterface $orderTransactionRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface      $transactionRepository
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $orderTransactionRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        $this->_registry = $registry;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->transactionRepository = $transactionRepository;
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

        /** @var \ZPay\Standard\Model\Transaction\Order $transaction */
        $transaction = $this->updateOrder($order, $result);
        $this->createOrderTransaction($order, $transaction);
    }

    /**
     * @param Order     $salesOrder
     * @param \stdClass $data
     *
     * @return \ZPay\Standard\Model\Transaction\Order
     *
     * @throws \Exception
     */
    private function updateOrder(Order $salesOrder, \stdClass $data)
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

    /**
     * @param Order                                  $salesOrder
     * @param \ZPay\Standard\Model\Transaction\Order $orderTransaction
     *
     * @return Order\Payment\Transaction
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createOrderTransaction(Order $salesOrder, \ZPay\Standard\Model\Transaction\Order $orderTransaction)
    {
        /** @var \Magento\Sales\Model\Order\Payment\Transaction $paymentTransaction */
        $paymentTransaction = $this->transactionRepository->create();
        $paymentTransaction->setOrder($salesOrder)
            ->setTxnId($orderTransaction->getZpayOrderId())
            ->setTxnType(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);

        $paymentTransaction->setData('additional_information', $orderTransaction->toArray([
            'quote_id',
            'order_id',
            'zpay_order_id',
            'zpay_quote_id',
            'zpay_address',
            'zpay_amount_to',
            'zpay_order_status',
            'zpay_payout_status'
        ]));

        $this->transactionRepository->save($paymentTransaction);

        return $paymentTransaction;
    }
}
