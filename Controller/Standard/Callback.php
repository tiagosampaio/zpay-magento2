<?php

namespace ZPay\Standard\Controller\Standard;

class Callback extends \Magento\Framework\App\Action\Action
{

    /** @var int */
    const RESULT_CODE_ERROR = 204;
    
    /** @var int */
    const RESULT_CODE_SUCCESS = 200;
    
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

        /** @var \ZPay\Standard\Api\Data\TransactionOrderInterface $transactionOrder */
        $transactionOrder = $this->transactionOrderRepository->getByZPayOrderId($zPayOrderId);

        if (!$transactionOrder || !$transactionOrder->getId()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Order not found.'));
        }
    
        try {
            /** @var \stdClass $resultObject */
            $resultObject = $this->api->getOrderStatus($transactionOrder->getZpayOrderId());
        } catch (\Exception $e) {
            return $this->createResult(self::RESULT_CODE_ERROR, __('Some error has occurred.'));
        }
    
        $paymentStatus = (string) $resultObject->payment_status;
        $orderStatus   = (string) $resultObject->order_status;

        return $this->processCallback($transactionOrder, $paymentStatus, $orderStatus);
    }
    
    /**
     * @param \ZPay\Standard\Api\Data\TransactionOrderInterface $transactionOrder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processCallback(
        \ZPay\Standard\Api\Data\TransactionOrderInterface $transactionOrder,
        $paymentStatus,
        $orderStatus
    ) {
        try {
            if (!$this->statusVerification->isPaid($paymentStatus)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Order is not paid yet.'));
            }
    
            if (!$this->statusVerification->isCompleted($orderStatus)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment status is not completed yet.'));
            }
            
            $order = $this->getOrder($transactionOrder->getOrderId());
            
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
        
            $transaction = $this->transaction
                ->addObject($order)
                ->addObject($invoice);
        
            $order->addCommentToStatusHistory(__('Order was invoiced by ZPay callback.'), true);
        
            $transaction->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->createResult(self::RESULT_CODE_ERROR, __($e->getMessage()));
        } catch (\Exception $e) {
            return $this->createResult(
                self::RESULT_CODE_ERROR,
                __('Some problem has occurred when trying to register a new invoice.')
            );
        }
    
        return $this->createResult(self::RESULT_CODE_SUCCESS);
    }
    
    /**
     * @param $orderId
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Sales\Model\Order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOrder($orderId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
    
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This order cannot be invoiced.')
            );
        }
        
        return $order;
    }
    
    /**
     * @param int    $code
     * @param string $message
     * @param string $typeClass
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function createResult(
        $code,
        $message = null,
        $typeClass = \Magento\Framework\Controller\ResultFactory::TYPE_RAW
    ) {
        /** @var \Magento\Framework\Controller\ResultInterface $result */
        $result = $this->resultFactory->create($typeClass);
        $result->setHttpResponseCode((int) $code);
        $result->setContents($message);
        
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
