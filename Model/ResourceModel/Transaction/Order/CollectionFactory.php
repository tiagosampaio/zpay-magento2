<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\ResourceModel\Transaction\Order;

/**
 * Class CollectionFactory
 *
 * @package ZPay\Standard\Model\ResourceModel\Transaction\Order
 */
class CollectionFactory extends \ZPay\Standard\Model\AbstractFactory
{
    /**
     * CollectionFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager, Collection::class);
    }
}
