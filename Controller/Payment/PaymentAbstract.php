<?php

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\Storage;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Pricing\Helper\Data as HelperPricing;
use ZPay\Standard\Api\TransactionOrderRepositoryInterface;
use ZPay\Standard\Api\ServiceApiInterface;
use ZPay\Standard\Model\Transaction\Order;

abstract class PaymentAbstract extends Action
{

    const CONFIRMED_ORDER_ID_KEY = 'just_confirmed_order_id';
    const ORDER_STATUS_PAID      = 'PAID';
    const ORDER_STATUS_UNPAID    = 'UNPAID';


    /** @var \ZPay\Standard\Model\Service\Api */
    protected $api;

    /** @var Storage */
    protected $storage;

    /** @var HelperPricing */
    protected $helperPricing;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var InvoiceService */
    protected $invoiceService;

    /** @var Transaction */
    protected $transaction;

    /** @var TransactionOrderRepositoryInterface */
    protected $transactionOrderRepository;

    /** @var InvoiceRepositoryInterface */
    protected $invoiceRepository;

    /**
     * PaymentAbstract constructor.
     */
    public function __construct(
        Context $context,
        ServiceApiInterface $api,
        Storage $storage,
        HelperPricing $helperPricing,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        TransactionOrderRepositoryInterface $transactionOrderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->api = $api;
        $this->storage = $storage;
        $this->helperPricing = $helperPricing;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->invoiceRepository = $invoiceRepository;

        parent::__construct($context);
    }


    /**
     * @return bool|Order
     */
    protected function getZPayOrder()
    {
        $orderId = (string) $this->_request->getParam('order');

        return $this->loadZPayOrder($orderId);
    }


    /**
     * @return bool|Order
     */
    protected function getConfirmedZPayOrder()
    {
        $orderId = $this->storage->getData(self::CONFIRMED_ORDER_ID_KEY);
        // $orderId = 'cee5a106-0d77-4e25-9920-91f061a39003';

        return $this->loadZPayOrder($orderId);
    }


    /**
     * @param string $orderId
     *
     * @return bool|Order
     */
    protected function loadZPayOrder($orderId)
    {
        if (!$this->validateOrderId($orderId)) {
            return false;
        }

        /** @var ORder $order */
        $order = $this->_objectManager->create(Order::class);
        $order->load($orderId, 'zpay_order_id');

        if (!$order->getId()) {
            return false;
        }

        return $order;
    }


    /**
     * @param string $orderId
     *
     * @return bool
     */
    protected function validateOrderId($orderId)
    {
        if (empty($orderId)) {
            return false;
        }

        return true;
    }


    /**
     * @param \stdClass $object
     *
     * @return bool
     */
    protected function validate($object)
    {
        try {
            if (!$object) {
                return false;
            }

            if (!$object->order_id) {
                return false;
            }

            if (!$object->quote_id) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

}
