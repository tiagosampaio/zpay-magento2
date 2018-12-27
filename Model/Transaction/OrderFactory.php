<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\Transaction;

/**
 * Class OrderFactory
 *
 * @package ZPay\Standard\Model\Transaction
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
