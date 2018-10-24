<?php

namespace ZPay\Standard\Controller\Standard;

class Callback extends \Magento\Framework\App\Action\Action
{

    /** @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface */
    private $transactionOrderRepository;

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    private $orderRepository;

    /** @var \Magento\Sales\Model\Service\InvoiceService */
    private $invoiceService;

    /** @var \Magento\Sales\Model\Order\InvoiceRepository */
    private $invoiceRepository;

    /** @var \Magento\Framework\DB\Transaction */
    private $transaction;

    /** @var \ZPay\Standard\Model\Service\Api */
    private $api;

    /** @var \ZPay\Standard\Api\TransactionStatusVerification */
    private $statusVerification;
    
    /** @var \ZPay\Standard\Model\Logger\LoggerInterface */
    private $logger;

    /**
     * Callback constructor.
     *
     * @param \Magento\Framework\App\Action\Context                  $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface            $orderRepository
     * @param \Magento\Sales\Api\InvoiceManagementInterface          $invoiceService
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface          $invoiceRepository
     * @param \Magento\Framework\DB\Transaction                      $transaction
     * @param \ZPay\Standard\Model\Logger\LoggerInterface            $logger
     * @param \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository
     * @param \ZPay\Standard\Api\ServiceApiInterface                 $api
     * @param \ZPay\Standard\Api\TransactionStatusVerification       $statusVerification
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceService,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \ZPay\Standard\Model\Logger\LoggerInterface $logger,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \ZPay\Standard\Api\ServiceApiInterface $api,
        \ZPay\Standard\Api\TransactionStatusVerification $statusVerification
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
        $this->transaction = $transaction;
        $this->logger = $logger;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->api = $api;
        $this->statusVerification = $statusVerification;

        parent::__construct($context);
    }
    
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->prepareRequestLogging();
        
        $zPayOrderId = $this->getOrderId();

        /** @var \ZPay\Standard\Api\Data\TransactionOrderInterface $zPayOrder */
        $zPayOrder = $this->transactionOrderRepository->getByZPayOrderId($zPayOrderId);

        if (!$zPayOrder || !$zPayOrder->getId()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Order not found.'));
        }

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        try {
            /** @var \stdClass $object */
            $object = $this->api->getOrderStatus($zPayOrderId);
        } catch (\Exception $e) {
            $result->setContents(__('Some error has occurred.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        $paymentStatus = (string) $object->payment_status;
        $orderStatus   = (string) $object->order_status;

        /** @todo Remove these lines below. It's for simulation tests only. */
        // $paymentStatus = \ZPay\Standard\Api\TransactionStatusVerification::PAYMENT_STATUS_PAID;
        // $orderStatus   = \ZPay\Standard\Api\TransactionStatusVerification::ORDER_STATUS_COMPLETED;

        if (!$this->statusVerification->isPaid($paymentStatus)) {
            $result->setContents(__('Order is not paid yet.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        if (!$this->statusVerification->isCompleted($orderStatus)) {
            $result->setContents(__('Payment status is not completed yet.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($zPayOrder->getOrderId());
    
        /**
         * Let's check if the order is in payment review first.
         * If so we need to set the state to processing because of the verification below.
         */
        if ($order->isPaymentReview()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }
    
        /**
         * If the order cannot be invoiced that's because it's not ready for invoice.
         */
        if (!$order || !$order->canInvoice()) {
            $result->setContents(__('This order cannot be invoiced.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        try {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();

            $transaction = $this->transaction
                ->addObject($order)
                ->addObject($invoice);
            
            $order->addCommentToStatusHistory(__('Order was invoiced by ZPay callback.'), true);

            $transaction->save();
        } catch (\Exception $e) {
            $result->setContents(__('Some problem has occurred when trying to register a new invoice.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        $result->setHttpResponseCode(200);

        return $result;
    }
    
    /**
     * Retrieve the order ID from request object.
     *
     * @return string|null
     */
    private function getOrderId()
    {
        /** @var string $orderId */
        $orderId = $this->getRequest()->getParam('order_id');
        
        if (!empty($orderId)) {
            return $orderId;
        }
        
        /** @var string $content */
        $content = $this->getRequest()->getContent();
        
        if (empty($content)) {
            return null;
        }
    
        try {
            $data = json_decode($content, true);
        } catch (\Exception $e) {
            return null;
        }
        
        if (!isset($data['order_id'])) {
            return null;
        }
        
        return $data['order_id'];
    }
    
    /**
     * @return $this
     */
    private function prepareRequestLogging()
    {
        $this->logger->info($this->getRequest()->getContent());
        return $this;
    }
}
