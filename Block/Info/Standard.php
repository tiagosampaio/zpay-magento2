<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
namespace ZPay\Standard\Block\Info;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Block\Info;

class Standard extends Info
{
    
    /**
     * @var string
     */
    protected $_template = 'ZPay_Standard::info/standard.phtml';

    /** @var ObjectManagerInterface */
    protected $objectManager;


    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }


    /**
     * @return \ZPay\Standard\Model\Transaction\Order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getZPayOrder()
    {
        /** @var \Magento\Sales\Model\Order\Payment $info */
        $payment = $this->getInfo();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        /** @var \ZPay\Standard\Model\Transaction\Order $zOrder */
        $zOrder = $this->objectManager->create(\ZPay\Standard\Model\Transaction\Order::class);
        $zOrder->load($order->getId(), 'order_id');

        return $zOrder;
    }

}
