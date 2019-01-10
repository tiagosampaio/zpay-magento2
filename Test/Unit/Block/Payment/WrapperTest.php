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

namespace ZPay\Standard\Test\Unit\Block\Payment;

use ZPay\Standard\Block\Payment\Wrapper;
use ZPay\Standard\Test\Unit\Block\BlockAbstract;

class WrapperTest extends BlockAbstract
{
    
    /**
     * @var Wrapper
     */
    private $block;
    
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    
    /**
     * @var \ZPay\Standard\Api\TransactionOrderRepositoryInterface
     */
    private $transOrderRepository;
    
    protected function setUp()
    {
        $this->mockObjects();
        
        $this->orderRepository = $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->orderRepository->method('get')->willReturn($this->order);
        
        $transactionOrder = $this->createMock(\ZPay\Standard\Api\Data\TransactionOrderInterface::class);
        
        $this->transOrderRepository = $this->createMock(\ZPay\Standard\Api\TransactionOrderRepositoryInterface::class);
        $this->transOrderRepository->method('getByOrderId')->willReturn($transactionOrder);
        
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->method('getParam')->willReturn('123');
        
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->context->method('getRequest')->willReturn($this->request);
        
        $this->block = $this->getObject(\ZPay\Standard\Block\Payment\Wrapper::class, [
            'context' => $this->context,
            'orderRepository' => $this->orderRepository,
            'transactionOrderRepository' => $this->transOrderRepository,
            'storage' => $this->getObject(\Magento\Framework\Session\Storage::class),
        ]);
    }
    
    protected function tearDown()
    {
        $this->block = null;
    }
    
    public function testConvertDate()
    {
        $input    = 'November 24th 2018 00:00:00';
        $expected = '2018-11-24 00:00:00';
        
        $this->block
            ->getTimezone()
            ->method('date')
            ->willReturn(new \DateTime($input));
        
        $this->assertEquals($expected, $this->block->convertDate($input));
    }
    
    public function testGetBtcRate()
    {
        $order = $this->orderRepository->get(123);
        $order->method('getId')
            ->willReturn('123');
        $order->method('getGrandTotal')
            ->willReturn(259.90); // Order Value.
    
        $this->transOrderRepository
            ->getByOrderId('123')
            ->method('getZpayAmountTo')
            ->willReturn(25990); // BitCoin Value.
        
        $this->assertEquals(0.01, $this->block->getBtcRate());
    }
}
