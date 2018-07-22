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
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceService,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $transactionOrderRepository,
        \ZPay\Standard\Api\ServiceApiInterface $api
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
        $this->transaction = $transaction;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->api = $api;

        parent::__construct($context);
    }

    public function execute()
    {
        $zPayOrderId = $this->getRequest()->getParam('order_id');
        // $referenceId = $this->getRequest()->getParam('reference_id');

        /** @var \ZPay\Standard\Api\Data\TransactionOrderInterface $zPayOrder */
        $zPayOrder = $this->transactionOrderRepository->getByZPayOrderId($zPayOrderId);

        if (!$zPayOrder || !$zPayOrder->getId()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Order not found.'));
        }

        try {
            /** @var \stdClass $object */
            $object = $this->api->getOrderStatus($zPayOrderId);
        } catch (\Exception $e) {
            return false;
        }

        $paymentStatus = (string) $object->payment_status;
        // $paymentStatus = \ZPay\Standard\Controller\Payment\PaymentAbstract::ORDER_STATUS_PAID;

        if ($paymentStatus !== \ZPay\Standard\Controller\Payment\PaymentAbstract::ORDER_STATUS_PAID) {
            /** @var \Magento\Framework\Controller\Result\Raw $result */
            $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
            $result->setHttpResponseCode(204);

            return $result;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($zPayOrder->getOrderId());

        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }

        if (!$order || !$order->canInvoice()) {
            return false;
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
            return false;
        }

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(200);

        return $result;
    }
}
