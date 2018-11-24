<?php

namespace ZPay\Standard\Test\Unit\Model\Transaction;

use ZPay\Standard\Test\Unit\UnitAbstract;
use ZPay\Standard\Model\Transaction\Order;

class OrderTest extends UnitAbstract
{
    /**
     * @var Order
     */
    protected $order;
    
    protected function setUp()
    {
        $this->order = $this->getObject(Order::class);
    
        $this->order
            ->setOrderId(123)
            ->setQuoteId(456)
            ->setZpayAddress('ABCDEF')
            ->setZpayAmountTo(2)
            ->setZpayOrderId(3)
            ->setZpayQuoteId(4)
            ->setZpayOrderStatus('FINISHED')
            ->setZpayPayoutStatus('CONFIRMED')
        ;
    }
    
    public function testGetOrderId()
    {
        $this->assertEquals(123, $this->order->getOrderId());
    }
    
    public function testGetQuoteId()
    {
        $this->assertEquals(456, $this->order->getQuoteId());
    }
    
    public function testGetZpayAddress()
    {
        $this->assertEquals('ABCDEF', $this->order->getZpayAddress());
    }
    
    public function testGetZpayAmountTo()
    {
        $this->assertEquals(2, $this->order->getZpayAmountTo());
    }
    
    public function testGetZpayOrderId()
    {
        $this->assertEquals(3, $this->order->getZpayOrderId());
    }
    
    public function testGetZpayQuoteId()
    {
        $this->assertEquals(4, $this->order->getZpayQuoteId());
    }
    
    public function testGetZpayOrderStatus()
    {
        $this->assertEquals('FINISHED', $this->order->getZpayOrderStatus());
    }
    
    public function testGetZpayPayoutStatus()
    {
        $this->assertEquals('CONFIRMED', $this->order->getZpayPayoutStatus());
    }
}
