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

    /** @var \ZPay\Standard\Api\TransactionVerification */
    private $transactionVerification;

    /**
     * Callback constructor.
     *
     * @param \Magento\Framework\App\Action\Context                  $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface            $orderRepository
     * @param \Magento\Sales\Api\InvoiceManagementInterface          $invoiceService
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface          $invoiceRepository
     * @param \Magento\Framework\DB\Transaction                      $transaction
     * @param \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository
     * @param \ZPay\Standard\Api\ServiceApiInterface                 $api
     * @param \ZPay\Standard\Api\TransactionVerification             $transactionVerification
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceService,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \ZPay\Standard\Api\ServiceApiInterface $api,
        \ZPay\Standard\Api\TransactionVerification $transactionVerification
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
        $this->transaction = $transaction;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->api = $api;
        $this->transactionVerification = $transactionVerification;

        parent::__construct($context);
    }

    public function execute()
    {
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
        // $paymentStatus = \ZPay\Standard\Controller\Payment\PaymentAbstract::PAYMENT_STATUS_PAID;
        // $orderStatus   = \ZPay\Standard\Controller\Payment\PaymentAbstract::ORDER_STATUS_COMPLETED;

        if (!$this->transactionVerification->isPaid($zPayOrder, $paymentStatus)) {
            $result->setContents(__('Order is not paid yet.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        if (!$this->transactionVerification->isCompleted($zPayOrder, $orderStatus)) {
            $result->setContents(__('Payment status is not completed yet.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($zPayOrder->getOrderId());

        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }

        if (!$order || !$order->canInvoice()) {
            $result->setContents(__('This order cannot be invoiced.'));
            $result->setHttpResponseCode(204);
            return $result;
        }

        try {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();

            $this->invoiceRepository->save($invoice);

            $transaction = $this->transaction
                ->addObject($order)
                ->addObject($invoice);

            $order->addStatusHistoryComment(__('Order was invoiced by ZPay callback.'), true);

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
     * @return string|null
     */
    private function getOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        
        if (!empty($orderId)) {
            return $orderId;
        }
        
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
}
