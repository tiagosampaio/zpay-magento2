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

namespace ZPay\Standard\Test\Unit\Block\Form;

class StandardTest extends \ZPay\Standard\Test\Unit\Block\BlockAbstract
{
    /**
     * @var \ZPay\Standard\Block\Form\Standard
     */
    private $block;
    
    protected function setUp()
    {
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->block = $this->getObject(\ZPay\Standard\Block\Form\Standard::class, [
            'context' => $context
        ]);
    }
    
    protected function tearDown()
    {
        $this->block = null;
    }
    
    public function testGetTemplate()
    {
        $template = 'ZPay_Standard::form/standard.phtml';
        $this->block->setTemplate($template);
        $this->assertEquals($template, $this->block->getTemplate());
    }
}
