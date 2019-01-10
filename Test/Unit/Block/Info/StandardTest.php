<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package ZPay\Standard
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Test\Unit\Block\Info;

class StandardTest extends \ZPay\Standard\Test\Unit\Block\BlockAbstract
{
    /**
     * @var \ZPay\Standard\Block\Info\Standard
     */
    private $block;
    
    protected function setUp()
    {
        $this->mockObjects();
        
        $this->block = $this->getMockBuilder(\ZPay\Standard\Block\Info\Standard::class)
            ->setConstructorArgs([$this->orderRepository, $this->context])
            ->getMock();
    
        $this->block
            ->method('getInfo')
            ->willReturn($this->payment);
    }
    
    public function testGetSalesOrderId()
    {
        $orderId = 123;
        
        $this->order
            ->method('getId')
            ->willReturn($orderId);
        
        $this->assertEquals($orderId, $this->block->getInfo()->getOrder()->getId());
    }
}
