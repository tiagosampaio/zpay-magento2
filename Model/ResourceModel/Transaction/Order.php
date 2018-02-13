<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
namespace ZPay\Standard\Model\ResourceModel\Transaction;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{

    const MAIN_TABLE = 'zpay_standard_transaction_order';


    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

}
