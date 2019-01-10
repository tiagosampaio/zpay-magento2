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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Order
 *
 * @package ZPay\Standard\Model\ResourceModel\Transaction
 */
class Order extends AbstractDb
{
    /**
     * @var string
     */
    const MAIN_TABLE = 'zpay_standard_transaction_order';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
