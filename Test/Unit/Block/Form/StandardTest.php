<?php

namespace ZPay\Standard\Test\Unit\Block\Form;

use ZPay\Standard\Block\Form\Standard;
use ZPay\Standard\Test\Unit\Block\BlockAbstract;

class StandardTest extends BlockAbstract
{

    /**
     * @test
     */
    public function checkIfBlockTemplateExistsAndIsValid()
    {
        /** @var Standard $block */
        $block = $this->getBlock(Standard::class);
        $this->assertNotEmpty($block->getTemplate());
    }

}
