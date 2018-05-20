<?php

namespace ZPay\Standard\Model;

use Magento\Framework\ObjectManagerInterface;

abstract class AbstractFactory
{

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = null)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
