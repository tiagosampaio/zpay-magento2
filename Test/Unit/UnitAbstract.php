<?php

namespace ZPay\Standard\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class UnitAbstract
 *
 * @package ZPay\Standard\Test\Unit
 */
abstract class UnitAbstract extends TestCase
{

    /** @var Bootstrap */
    protected $bootstrap = null;


    /**
     * @return $this
     */
    protected function init()
    {
        $this->bootstrap = Bootstrap::create(BP, $_SERVER);
        return $this;
    }


    /**
     * @return string
     */
    protected function getModuleName()
    {
        return 'ZPay_Standard';
    }


    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        $this->init();

        $objectManager = $this->bootstrap->getObjectManager();
        $state         = $objectManager->create(\Magento\Framework\App\State::class);
        $state->setAreaCode(Area::AREA_FRONTEND);

        return $objectManager;
    }


    /**
     * @param string $class
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function createObject($class, array $arguments = [])
    {
        return $this->getObjectManager()->create($class, $arguments);
    }


    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function getObject($class)
    {
        return $this->getObjectManager()->get($class);
    }

}
