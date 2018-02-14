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
            return false;
        }

        /** @var \stdClass $status */
        $object = $this->api->getOrderStatus($order->getZpayOrderId());

        if (!$this->validate($object)) {
            return false;
        }

        $paymentStatus = (string) $object->payment_status;

        /** @var \Magento\Sales\Model\Order $salesOrder */
        $salesOrder = $this->orderRepository->get($order->getOrderId());

        if ($paymentStatus == self::ORDER_STATUS_UNPAID) {
            $this->storage->setData('current_order_id', $salesOrder->getId());

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getUrl('*/*/pay'));

            return $resultRedirect;
        }

        if ($salesOrder->canInvoice()) {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->invoiceService->prepareInvoice($salesOrder);
            $invoice->register()
                ->save();

            $transaction = $this->transaction
                ->addObject($salesOrder)
                ->addObject($invoice);

            $transaction->save();
        }

        $this->storage->unsetData(self::CONFIRMED_ORDER_ID_KEY);
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
