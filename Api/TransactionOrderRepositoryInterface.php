<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use ZPay\Standard\Api\Data\TransactionOrderInterface;

/**
 * Interface TransactionOrderRepositoryInterface
 *
 * @package ZPay\Standard\Api
 */
interface TransactionOrderRepositoryInterface
{
    /**
     * @param TransactionOrderInterface $order
     *
     * @return TransactionOrderInterface
     */
    public function save(TransactionOrderInterface $order);
    
    /**
     * @param string $zpayOrderId
     *
     * @return TransactionOrderInterface
     */
    public function get($zpayOrderId);
    
    /**
     * @param string      $zpayOrderId
     * @param null|string $orderStatus
     * @param null|string $paymentStatus
     *
     * @return mixed
     */
    public function updateStatus($zpayOrderId, $orderStatus = null, $paymentStatus = null);
    
    /**
     * @param string $transactionOrderId
     *
     * @return TransactionOrderInterface
     */
    public function getById($transactionOrderId);
    
    /**
     * @param string $orderId
     *
     * @return TransactionOrderInterface
     */
    public function getByZPayOrderId($orderId);
    
    /**
     * @param string $orderId
     *
     * @return TransactionOrderInterface
     */
    public function getByOrderId($orderId);
    
    /**
     * @param TransactionOrderInterface $order
     *
     * @return $this
     */
    public function delete(TransactionOrderInterface $order);
    
    /**
     * @param $transactionOrderId
     *
     * @return $this
     */
    public function deleteById($transactionOrderId);
    
    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
