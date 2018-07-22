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

    /** @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface */
    private $orderRepository;

    public function __construct(
        \ZPay\Standard\Api\TransactionOrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
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
        $zOrder = $this->orderRepository->getByOrderId($order->getId());

        return $zOrder;
    }
}
