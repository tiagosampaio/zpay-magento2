<?php
/**
 * @author Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

namespace ZPay\Standard\Model\Payment\Method;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

/**
 * Class Standard
 *
 * @package ZPay\Standard\Model\Payment\Method
 */
class Standard extends \Magento\Payment\Model\Method\AbstractMethod
{

    const PAYMENT_METHOD_CODE = 'zpay_standard';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /** @var bool */
    protected $_canOrder = true;

    /** @var string */
    protected $_formBlockType = \ZPay\Standard\Block\Form\Standard::class;

    /** @var string */
    protected $_infoBlockType = \ZPay\Standard\Block\Info\Standard::class;

    /** @var \Magento\Framework\UrlInterface */
    private $urlBuilder = null;

    /** @var \Magento\Framework\App\RequestInterface */
    private $request = null;

    /** @var \Magento\Framework\Session\StorageInterface */
    private $storage = null;

    /** @var null|\ZPay\Standard\Helper\Data */
    private $helper = null;

    /** @var \ZPay\Standard\Model\Service\Api */
    private $api;

    /**
     * Standard constructor.
     *
     * @param \ZPay\Standard\Helper\Data                           $helper
     * @param \ZPay\Standard\Model\Service\Api                     $api
     * @param \Magento\Framework\Model\Context                     $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory    $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory         $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                         $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                 $logger
     * @param \Magento\Framework\UrlInterface                      $urlBuilder
     * @param \Magento\Framework\App\RequestInterface              $request
     * @param \Magento\Framework\Session\StorageInterface          $storage
     * @param array                                                $data
     */
    public function __construct(
        \ZPay\Standard\Helper\Data $helper,
        \ZPay\Standard\Model\Service\Api $api,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Session\StorageInterface $storage,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->helper = $helper;
        $this->api = $api;
        $this->storage = $storage;
    }

    /**
     * @param DataObject $data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);

        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float                                       $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        }

        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float                                       $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float                                       $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function order(InfoInterface $payment, $amount)
    {
        parent::canOrder();

        /** @var Order $order */
        $order = $payment->getOrder();

        /** @var \stdClass $result */
        $result = $this->api->createOrder($order);
        $result->salesOrder = $order;

        /**
         * At this point we still don't have the order id to link the reference to.
         * So we can register the result to save the reference after order is placed and saved in database.
         *
         * @see \ZPay\Standard\Observer\RegisterNewOrder::execute()
         */
        $order->setData('zpay_api_result', $result);
        // $this->_registry->register('zpay_api_current_result', $result, true);

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('zpay/standard/redirect', [
            '_secure' => $this->request->isSecure()
        ]);
    }

    /**
     * @param \stdClass $result
     *
     * @return $this
     */
    protected function registerSession(\stdClass $result)
    {
        $this->storage->init([
            'address'   => $result->address,
            'order_id'  => $result->order_id,
            'quote_id'  => $result->quote_id,
            'time'      => $result->time,
            'amount_to' => $result->amount_to,
            'timestamp' => $result->timestamp,
        ]);

        return $this;
    }
}
