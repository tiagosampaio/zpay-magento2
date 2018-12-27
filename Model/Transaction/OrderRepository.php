<?php

namespace ZPay\Standard\Model\Transaction;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsFactory;
use ZPay\Standard\Api\Data\TransactionOrderInterface;
use ZPay\Standard\Api\TransactionOrderRepositoryInterface;
use ZPay\Standard\Model\Transaction\OrderFactory as TransactionOrderModelFactory;
use ZPay\Standard\Model\ResourceModel\Transaction\OrderFactory as TransactionOrderResourceModelFactory;
use ZPay\Standard\Model\ResourceModel\Transaction\Order\CollectionFactory as TransactionOrderCollectionFactory;

class OrderRepository implements TransactionOrderRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;
    
    /**
     * @var TransactionOrderResourceModelFactory
     */
    private $transactionOrderResourceFactory;
    
    /**
     * @var TransactionOrderModelFactory
     */
    private $transactionOrderModelFactory;
    
    /**
     * @var TransactionOrderCollectionFactory
     */
    private $transactionOrderCollectionFactory;
    
    /**
     * @var StatusVerification
     */
    private $statusVerification;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        StatusVerification $statusVerification,
        TransactionOrderResourceModelFactory $transactionOrderResourceFactory,
        TransactionOrderModelFactory $transactionOrderModelFactory,
        TransactionOrderCollectionFactory $transactionOrderCollectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->statusVerification = $statusVerification;
        $this->collectionProcessor = $collectionProcessor;
        $this->transactionOrderResourceFactory = $transactionOrderResourceFactory;
        $this->transactionOrderModelFactory = $transactionOrderModelFactory;
        $this->transactionOrderCollectionFactory = $transactionOrderCollectionFactory;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     *
     * @return TransactionOrderInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(TransactionOrderInterface $transactionOrder)
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $transactionOrder */
        $this->getResource()->save($transactionOrder);
        return $transactionOrder;
    }

    /**
     * @param int $transactionId
     *
     * @return TransactionOrderInterface
     */
    public function get($transactionId)
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $transactionOrder */
        $transactionOrder = $this->modelInstance()->setId($transactionId);
        $this->getResource()->load($transactionOrder, $transactionId);

        return $transactionOrder;
    }

    /**
     * @param string $id
     *
     * @return TransactionOrderInterface
     */
    public function getById($transactionId)
    {
        return $this->get($transactionId);
    }

    /**
     * @param int $orderId
     * @return TransactionOrderInterface
     */
    public function getByZPayOrderId($orderId)
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $transactionOrder */
        $transactionOrder = $this->modelInstance()->setZpayOrderId($orderId);
        $this->getResource()->load($transactionOrder, $orderId, 'zpay_order_id');

        return $transactionOrder;
    }

    /**
     * @param int $orderId
     * @return TransactionOrderInterface
     */
    public function getByOrderId($orderId)
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $transactionOrder */
        $transactionOrder = $this->modelInstance()->setZpayOrderId($orderId);
        $this->getResource()->load($transactionOrder, $orderId, 'order_id');

        return $transactionOrder;
    }

    /**
     * @param TransactionOrderInterface $transactionOrder
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function delete(TransactionOrderInterface $transactionOrder)
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $transactionOrder */
        $this->getResource()->delete($transactionOrder);
        return $this;
    }

    /**
     * @param int $transactionOrderId
     *
     * @return TransactionOrderRepositoryInterface|OrderRepository
     *
     * @throws \Exception
     */
    public function deleteById($transactionOrderId)
    {
        /** @var TransactionOrderInterface $transactionOrder */
        $transactionOrder = $this->modelInstance()->setId($transactionOrderId);
        return $this->delete($transactionOrder);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \ZPay\Standard\Model\ResourceModel\Transaction\Order\Collection $collection */
        $collection = $this->collectionInstance();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
    
    /**
     * @param string $zpayOrderId
     * @param null   $orderStatus
     * @param null   $paymentStatus
     *
     * @return mixed|void
     */
    public function updateStatus($zpayOrderId, $orderStatus = null, $paymentStatus = null)
    {
        /** @var \ZPay\Standard\Api\Data\TransactionOrderInterface $transaction */
        $transaction = $this->getByZPayOrderId($zpayOrderId);
        
        if ($orderStatus && $this->statusVerification->isOrderStatusValid($orderStatus)) {
            $transaction->setZpayOrderStatus($orderStatus);
        }
        
        if ($paymentStatus && $this->statusVerification->isPaymentStatusValid($paymentStatus)) {
            $transaction->setZpayPayoutStatus($paymentStatus);
        }
        
        try {
            $this->save($transaction);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return \ZPay\Standard\Model\ResourceModel\Transaction\Order
     */
    protected function getResource()
    {
        return $this->transactionOrderResourceFactory->create();
    }

    /**
     * @return TransactionOrderInterface
     */
    protected function modelInstance()
    {
        return $this->transactionOrderModelFactory->create();
    }

    /**
     * @return \ZPay\Standard\Model\ResourceModel\Transaction\Order\Collection
     */
    protected function collectionInstance()
    {
        return $this->transactionOrderCollectionFactory->create();
    }
}
