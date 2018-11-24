<?php

namespace ZPay\Standard\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class UnitAbstract
 *
 * @package ZPay\Standard\Test\Unit
 */
abstract class UnitAbstract extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
    
    /**
     * @param string $className
     * @param array  $arguments
     *
     * @return object
     */
    protected function getObject($className, array $arguments = [])
    {
        return (new ObjectManager($this))->getObject($className, $arguments);
    }
    
    protected function mockObjects()
    {
        $this->mockSalesOrderPayment();
    
        $this->orderRepository = $this->getMockBuilder(\ZPay\Standard\Model\Transaction\OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedSalesOrder()
    {
        $this->mockSalesOrder();
        return $this->order;
    }
    
    private function mockSalesOrder()
    {
        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    protected function getMockedSalesOrderPayment()
    {
        $this->mockSalesOrderPayment();
        return $this->payment;
    }
    
    private function mockSalesOrderPayment()
    {
        $this->payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->payment
            ->method('getOrder')
            ->willReturn($this->getMockedSalesOrder());
    }
}
