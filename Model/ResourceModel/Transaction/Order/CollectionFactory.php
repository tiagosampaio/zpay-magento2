<?php

namespace ZPay\Standard\Model\ResourceModel\Transaction\Order;

use Magento\Framework\ObjectManagerInterface;
use ZPay\Standard\Model\AbstractFactory;

class CollectionFactory extends AbstractFactory
{

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager, Collection::class);
    }
}
