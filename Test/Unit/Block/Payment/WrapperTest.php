<?php

namespace ZPay\Standard\Test\Unit\Block\Payment;

use ZPay\Standard\Block\Payment\Wrapper;
use ZPay\Standard\Test\Unit\Block\BlockAbstract;

class WrapperTest extends BlockAbstract
{
    
    /**
     * @var Wrapper
     */
    private $block;
    
    protected function setUp()
    {
        $this->block = $this->getObject(\ZPay\Standard\Block\Payment\Wrapper::class, [
        ]);
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
}
