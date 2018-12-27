<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

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

/**
 * Class PaymentAbstract
 *
 * @package ZPay\Standard\Controller\Payment
 */
abstract class PaymentAbstract extends Action
{
    /**
     * @var string
     */
    const CONFIRMED_ORDER_ID_KEY = 'just_confirmed_order_id';
    
    /**
     * @var \ZPay\Standard\Model\Service\Api
     */
    protected $api;
    
    /**
     * @var Storage
     */
    protected $storage;
    
    /**
     * @var HelperPricing
     */
    protected $helperPricing;
    
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    
    /**
     * @var Transaction
     */
    protected $transaction;
    
    /**
     * @var TransactionOrderRepositoryInterface
     */
    protected $transactionOrderRepository;
    
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;
    
    /**
     * @var \ZPay\Standard\Api\TransactionStatusVerification
     */
    protected $statusVerification;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * PaymentAbstract constructor.
     *
     * @param Context                                            $context
     * @param ServiceApiInterface                                $api
     * @param Storage                                            $storage
     * @param HelperPricing                                      $helperPricing
     * @param OrderRepositoryInterface                           $orderRepository
     * @param InvoiceService                                     $invoiceService
     * @param Transaction                                        $transaction
     * @param TransactionOrderRepositoryInterface                $transactionOrderRepository
     * @param InvoiceRepositoryInterface                         $invoiceRepository
     * @param \ZPay\Standard\Api\TransactionStatusVerification   $statusVerification
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        InvoiceRepositoryInterface $invoiceRepository,
        \ZPay\Standard\Api\TransactionStatusVerification $statusVerification,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->api = $api;
        $this->storage = $storage;
        $this->helperPricing = $helperPricing;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->transactionOrderRepository = $transactionOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->statusVerification = $statusVerification;
        $this->scopeConfig = $scopeConfig;
        
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
        
        return $this->loadZPayOrder($orderId);
    }
    
    /**
     * @param string $orderId
     *
     * @return bool|\ZPay\Standard\Api\Data\TransactionOrderInterface
     */
    protected function loadZPayOrder($orderId)
    {
        if (!$this->validateOrderId($orderId)) {
            return false;
        }
        
        $transactionOrder = $this->transactionOrderRepository->getByZPayOrderId($orderId);
        
        if (!$transactionOrder->getId()) {
            return false;
        }
        
        return $transactionOrder;
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
