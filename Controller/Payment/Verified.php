<?php

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use ZPay\Standard\Model\Transaction\Order;

class Verified extends Verify
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Order $order */
        $order = $this->getConfirmedZPayOrder();

        if (!$order || !$order->getId()) {
            return $this->_redirect('');
        }

        try {
            /** @var \stdClass $status */
            $object = $this->api->getOrderStatus($order->getZpayOrderId());
        } catch (\Exception $e) {
            return $this->_redirect('');
        }

        if (!$this->validate($object)) {
            return $this->_redirect('');
        }

        $paymentStatus = (string) $object->payment_status;
        $order->setZpayPayoutStatus($paymentStatus);

        $this->transactionOrderRepository->save($order);

        /** @var \Magento\Sales\Model\Order $salesOrder */
        $salesOrder = $this->orderRepository->get($order->getOrderId());

        if ($paymentStatus !== self::ORDER_STATUS_PAID) {
            $this->storage->setData('current_order_id', $salesOrder->getId());

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getUrl('*/*/pay'));

            return $resultRedirect;
        }

        /**
         * At this point the payment is not confirmed in the service yet.
         * It takes about 30 minutes to confirm so when it happens a callback is called.
         */
        $salesOrder->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
        $this->orderRepository->save($salesOrder);

//        if ($salesOrder->canInvoice()) {
//            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
//            $invoice = $this->invoiceService->prepareInvoice($salesOrder);
//            $invoice->register();
//
//            $this->invoiceRepository->save($invoice);
//
//            $transaction = $this->transaction
//                ->addObject($salesOrder)
//                ->addObject($invoice);
//
//            $transaction->save();
//        }

        $this->storage->unsetData(self::CONFIRMED_ORDER_ID_KEY);
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
