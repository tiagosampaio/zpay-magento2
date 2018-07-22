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
     * @param TransactionOrderInterface $order
     *
     * @return TransactionOrderInterface
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(TransactionOrderInterface $order)
    {
        $this->getResource()->save($order);
        return $order;
    }

    /**
     * @param int $id
     *
     * @return TransactionOrderInterface
     */
    public function get($id)
    {
        /** @var TransactionOrderInterface $transactionOrder */
        $transactionOrder = $this->modelInstance()->setId($id);
        $this->getResource()->load($transactionOrder, $id);

        return $transactionOrder;
    }

    /**
     * @param string $id
     *
     * @return TransactionOrderInterface
     */
    public function getById($id)
    {
        return $this->get($id);
    }

    /**
     * @param $orderId
     * @return TransactionOrderInterface
     */
    public function getByZPayOrderId($orderId)
    {
        /** @var TransactionOrderInterface $transactionOrder */
        $order = $this->modelInstance()->setZpayOrderId($orderId);
        $this->getResource()->load($order, $orderId, 'zpay_order_id');

        return $order;
    }

    /**
     * @param $orderId
     * @return TransactionOrderInterface
     */
    public function getByOrderId($orderId)
    {
        /** @var TransactionOrderInterface $transactionOrder */
        $order = $this->modelInstance()->setZpayOrderId($orderId);
        $this->getResource()->load($order, $orderId, 'order_id');

        return $order;
    }

    /**
     * @param TransactionOrderInterface $order
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function delete(TransactionOrderInterface $order)
    {
        $this->getResource()->delete($order);
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
