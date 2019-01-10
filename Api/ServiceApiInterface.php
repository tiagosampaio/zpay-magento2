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

namespace ZPay\Standard\Api;

/**
 * Interface ServiceApiInterface
 *
 * @package ZPay\Standard\Api
 */
interface ServiceApiInterface
{
    /**
     * @return $this
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function init();
    
    /**
     * @param string $contractId
     *
     * @return $this
     */
    public function setContractId($contractId);
    
    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username);
    
    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password);
    
    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment = \ZPay\Standard\Model\Config\Source\Environment::PRODUCTION);
    
    /**
     * @return mixed
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function getToken();
    
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \stdClass
     *
     * @throws \ZPay\Standard\Exception\InvalidObjectException
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrder(\Magento\Sales\Api\Data\OrderInterface $order);
    
    /**
     * @param string $orderId
     *
     * @return bool|\stdClass
     *
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function renewOrder($orderId);
    
    /**
     * @param string $zpayOrderId
     *
     * @return \stdClass
     *
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function getOrderStatus($zpayOrderId);
}
