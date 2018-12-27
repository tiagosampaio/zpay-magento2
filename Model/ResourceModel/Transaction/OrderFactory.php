<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\ResourceModel\Transaction;

/**
 * Class OrderFactory
 *
 * @package ZPay\Standard\Model\ResourceModel\Transaction
 */
class OrderFactory extends \ZPay\Standard\Model\AbstractFactory
{
    /**
     * OrderFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager, Order::class);
    }
}
