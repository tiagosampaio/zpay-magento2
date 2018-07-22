<?php

namespace ZPay\Standard\Model\Transaction;

use Magento\Framework\ObjectManagerInterface;
use ZPay\Standard\Model\AbstractFactory;

class OrderFactory extends AbstractFactory
{

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager, Order::class);
    }
}
