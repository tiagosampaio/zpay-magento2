<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Model\ResourceModel\Transaction\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use ZPay\Standard\Model\Transaction\Order;
use ZPay\Standard\Model\ResourceModel\Transaction\Order as ResourceOrder;

/**
 * Class Collection
 *
 * @package ZPay\Standard\Model\ResourceModel\Transaction\Order
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Order::class, ResourceOrder::class);
    }
}
