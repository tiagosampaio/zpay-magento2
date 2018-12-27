<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\Service\Request\Body;

/**
 * Class OrderFactory
 *
 * @package ZPay\Standard\Model\Service\Request\Body
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
