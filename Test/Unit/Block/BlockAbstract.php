<?php

namespace ZPay\Standard\Test\Unit\Block;

use Magento\Framework\App\Area;
use ZPay\Standard\Test\Unit\UnitAbstract;

abstract class BlockAbstract extends UnitAbstract
{

    /**
     * @param string $class
     * @param string $area
     *
     * @return \Magento\Framework\View\Element\Template
     */
    protected function getBlock($class, $area = Area::AREA_FRONTEND)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->createObject($class);
        $block->setArea($area);
        $block->setModuleName($this->getModuleName());

        return $block;
    }

}
