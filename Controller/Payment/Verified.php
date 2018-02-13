<?php

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order as SalesOrder;
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

        $payoutStatus = (string) $object->payout_status;

        if ($payoutStatus == self::ORDER_STATUS_UNPAID) {
            /** @var SalesOrder $salesOrder */
            $salesOrder = $this->_objectManager->create(SalesOrder::class);
            $salesOrder->load($order->getOrderId());
            $this->storage->setData('current_order_id', $salesOrder->getId());

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getUrl('*/*/pay'));

            return $resultRedirect;
        }

        $this->storage->unsetData(self::CONFIRMED_ORDER_ID_KEY);
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

}
