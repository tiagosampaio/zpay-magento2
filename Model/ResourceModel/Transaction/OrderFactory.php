<?php

namespace ZPay\Standard\Model\ResourceModel\Transaction;

use Magento\Framework\ObjectManagerInterface;
use ZPay\Standard\Model\AbstractFactory;

class OrderFactory extends AbstractFactory
{

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager, Order::class);
    }
}
