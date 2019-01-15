<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package  ZPay\Standard
 * @author   Tiago Sampaio <tiago@tiagosampaio.com>
 * @link     https://github.com/tiagosampaio
 * @link     https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Pricing\Helper\Data as HelperPricing;
use Magento\Framework\Session\Storage;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use ZPay\Standard\Api\ServiceApiInterface;
use ZPay\Standard\Api\TransactionOrderRepositoryInterface;

/**
 * Class Update
 *
 * @package ZPay\Standard\Controller\Payment
 */
class Update extends PaymentAbstract
{
    /**
     * @var \ZPay\Standard\Model\TimeCalculator
     */
    private $timeCalculator;

    public function __construct(
        Context $context,
        ServiceApiInterface $api,
        Storage $storage,
        HelperPricing $helperPricing,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        Transaction $transaction,
        TransactionOrderRepositoryInterface $transactionOrderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        \ZPay\Standard\Api\TransactionStatusVerification $statusVerification,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ZPay\Standard\Model\TimeCalculator $timeCalculator
    ) {
        parent::__construct(
            $context,
            $api,
            $storage,
            $helperPricing,
            $orderRepository,
            $invoiceService,
            $transaction,
            $transactionOrderRepository,
            $invoiceRepository,
            $statusVerification,
            $scopeConfig
        );

        $this->timeCalculator = $timeCalculator;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function execute()
    {
        /** @var \ZPay\Standard\Model\Transaction\Order $order */
        $order = $this->getZPayOrder();

        if (!$order || !$order->getId()) {
            return false;
        }

        /** @var bool|\stdClass $result */
        $data = (object) $this->api->renewOrder($order->getZpayOrderId());

        if (!$this->validate($data)) {
            return false;
        }

        try {
            $order->setZpayOrderId((string) $data->order_id)
                ->setZpayQuoteId((string) $data->quote_id)
                ->setZpayAmountTo((float) $data->amount_to)
                ->setZpayAddress((string) $data->address)
                ->setZpayTime((int) $data->time)
                ->setZpayTimestamp(strtotime($data->timestamp));

            $this->transactionOrderRepository->save($order);

            /** @var \Magento\Sales\Model\Order $salesOrder */
            $salesOrder = $this->orderRepository->get($order->getOrderId());

            $grandTotal = (float) $salesOrder->getGrandTotal();
            $bitcoinRate = (float) ($grandTotal / $order->getZpayAmountTo());

            $data->total_brl = $this->helperPricing->currency($grandTotal, true, false);
            $data->rate = $this->helperPricing->currency($bitcoinRate, true, false);

            $timestamp = $this->timeCalculator->calculate($data->timestamp, $data->time);

            $data->timestamp = $timestamp->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

            $data = (array) $data;
            unset($data['refresh_token']);

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($data);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param \stdClass $object
     *
     * @return bool
     */
    protected function validate($object)
    {
        try {
            if (!parent::validate($object)) {
                return false;
            }

            if (!$object->address) {
                return false;
            }

            if (!$object->amount_to) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
