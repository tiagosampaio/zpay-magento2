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

namespace ZPay\Standard\Model\Transaction;

use ZPay\Standard\Api\CallbackProcessorInterface;
use ZPay\Standard\Api\Data\TransactionOrderInterface;
use ZPay\Standard\Exception\LocalizedException;

/**
 * Class CallbackProcessor
 *
 * @package ZPay\Standard\Model\Transaction
 */
class CallbackProcessor implements CallbackProcessorInterface
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \ZPay\Standard\Model\Logger\LoggerInterface
     */
    protected $logger;

    /**
     * @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface
     */
    protected $transactionOrderRepository;

    /**
     * @var \ZPay\Standard\Api\ServiceApiInterface
     */
    protected $api;

    /**
     * @var \ZPay\Standard\Api\TransactionStatusVerification
     */
    protected $statusVerification;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceService,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \ZPay\Standard\Model\Logger\LoggerInterface $logger,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \ZPay\Standard\Api\ServiceApiInterface $api,
        \ZPay\Standard\Api\TransactionStatusVerification $statusVerification
    ) {
        $this->context = $context;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
        $this->transactionFactory = $transactionFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->api = $api;
        $this->statusVerification = $statusVerification;
    }

    /**
     * @inheritdoc
     */
    public function processCallback($zPayOrderId)
    {
        /** @var TransactionOrderInterface $transactionOrder */
        $transactionOrder = $this->transactionOrderRepository->getByZPayOrderId($zPayOrderId);

        if (!$transactionOrder || !$transactionOrder->getId()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Order not found.'));
        }

        try {
            /** @var \stdClass $resultObject */
            $resultObject = (object) $this->api->getOrderStatus($transactionOrder->getZpayOrderId());
        } catch (\Exception $e) {
            throw new LocalizedException(__('Some error has occurred.'), self::RESULT_CODE_ERROR);
        }

        $paymentStatus = (string) $resultObject->payment_status;
        $orderStatus = (string) $resultObject->order_status;

        if (!$this->statusVerification->isPaid($paymentStatus)) {
            throw new LocalizedException(__('Order is not paid yet.'), self::RESULT_PAYMENT_REQUIRED);
        }

        if (!$this->statusVerification->isCompleted($orderStatus)) {
            throw new LocalizedException(
                __('Order status is not completed yet. Current order Status %1.', $orderStatus),
                self::RESULT_PROCESSING
            );
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder($transactionOrder->getOrderId());

        $this->canInvoiceOrder($order);

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($order)
            ->addObject($invoice);

        $order->addCommentToStatusHistory(__('Order was invoiced by ZPay callback.'), true);

        $transaction->save();

        $this->transactionOrderRepository->updateStatus(
            $transactionOrder->getZpayOrderId(),
            $orderStatus,
            $paymentStatus
        );
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Sales\Model\Order
     *
     * @throws LocalizedException
     */
    private function getOrder($orderId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        /**
         * If the order is empty it means that this order ID does not exist.
         */
        if (!$order) {
            throw new LocalizedException(__('This order does not exist.'), self::RESULT_NOT_FOUND);
        }

        return $order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @throws LocalizedException
     */
    private function canInvoiceOrder(\Magento\Sales\Model\Order $order)
    {
        /**
         * Let's check if the order is in payment review first.
         * If so we need to set the state to processing because of the verification below.
         */
        if ($order->isPaymentReview()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }

        /**
         * Check if the order was already invoiced before.
         */
        if (!$order->canInvoice() && $order->getInvoiceCollection()->getSize()) {
            throw new LocalizedException(__('This order was already invoiced.'), self::RESULT_CODE_ERROR);
        }

        /**
         * If the order cannot be invoiced that's because it's not ready for invoice.
         */
        if (!$order->canInvoice()) {
            throw new LocalizedException(__('This order cannot be invoiced.'), self::RESULT_CODE_ERROR);
        }
    }
}
