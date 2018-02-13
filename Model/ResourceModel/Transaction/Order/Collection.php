<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use ZPay\Standard\Model\Transaction\Order;
use ZPay\Standard\Model\ResourceModel\Transaction\Order as ResourceOrder;

class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init(Order::class, ResourceOrder::class);
    }

}
