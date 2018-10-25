<?php

namespace ZPay\Standard\Model\Service\Request\Body;

class OrderFactory extends \ZPay\Standard\Model\AbstractFactory
{

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager, Order::class);
    }
}
