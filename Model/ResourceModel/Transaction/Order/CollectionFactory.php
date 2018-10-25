<?php

namespace ZPay\Standard\Model\ResourceModel\Transaction\Order;

class CollectionFactory extends \ZPay\Standard\Model\AbstractFactory
{

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager, Collection::class);
    }
}
