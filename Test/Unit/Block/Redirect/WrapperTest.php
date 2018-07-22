<?php

namespace ZPay\Standard\Test\Unit\Block\Redirect;

use Magento\Sales\Model\Order;
use ZPay\Standard\Block\Payment\Wrapper;
use ZPay\Standard\Test\Unit\Block\BlockAbstract;
use Magento\Checkout\Model\Session as CheckoutSession;

class WrapperTest extends BlockAbstract
{

    /**
     * @test
     */
    public function checkIfBlockTemplateExistsAndIsValid()
    {
        /** @var Wrapper $block */
        $block = $this->getBlock(Wrapper::class);
        $this->assertNotEmpty($block->getTemplate());
    }


    /**
     * @test
     */
    public function checkCheckoutSessionInstance()
    {
        /** @var Wrapper $block */
        $block = $this->getBlock(Wrapper::class);
        $this->assertInstanceOf(CheckoutSession::class, $block->getCheckoutSession());
    }


    /**
     * @test
     */
    public function checkSalesOrderInstance()
    {
        /** @var Wrapper $block */
        $block = $this->getBlock(Wrapper::class);
        $this->assertInstanceOf(Order::class, $block->getLastOrder());
    }
}
