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

namespace ZPay\Standard\Model\ResourceModel\Transaction;

/**
 * Class OrderFactory
 *
 * @package ZPay\Standard\Model\ResourceModel\Transaction
 */
class OrderFactory extends \ZPay\Standard\Model\AbstractFactory
{
    /**
     * OrderFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager, Order::class);
    }
}
