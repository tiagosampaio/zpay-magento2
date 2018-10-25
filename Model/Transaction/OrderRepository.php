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

    /** @var CollectionProcessorInterface */
    protected $collectionProcessor;

    /** @var SearchResultsFactory */
    protected $searchResultsFactory;

    /** @var TransactionOrderResourceModelFactory */
    protected $transactionOrderResourceFactory;

    /** @var TransactionOrderModelFactory */
    protected $transactionOrderModelFactory;

    /** @var TransactionOrderCollectionFactory */
    protected $transactionOrderCollectionFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        TransactionOrderResourceModelFactory $transactionOrderResourceFactory,
        TransactionOrderModelFactory $transactionOrderModelFactory,
        TransactionOrderCollectionFactory $transactionOrderCollectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
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
