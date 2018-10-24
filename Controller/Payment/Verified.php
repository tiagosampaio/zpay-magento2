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

        /** @todo Remove it. It's used only to simulate paid transactions. */
        // $paymentStatus = \ZPay\Standard\Api\TransactionStatusVerification::PAYMENT_STATUS_PAID;

        $order->setZpayPayoutStatus($paymentStatus);

        $this->transactionOrderRepository->save($order);

        /** @var \Magento\Sales\Model\Order $salesOrder */
        $salesOrder = $this->orderRepository->get($order->getOrderId());

        /**
         * Order was already set to PAYMENT REVIEW state.
         */
        if ($salesOrder->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            return $this->_redirect('customer/account');
        }

        if (!$this->statusVerification->isPaid($paymentStatus)) {
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
        $salesOrder->addCommentToStatusHistory(
            __('The order was confirmed by ZPay. Payment is being confirmed.'), true
        );
        $this->orderRepository->save($salesOrder);

        $this->storage->unsetData(self::CONFIRMED_ORDER_ID_KEY);

        /** @var \Magento\Framework\View\Result\Page $page */
        $page    = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $message = $this->scopeConfig->getValue(
            'payment/zpay_standard/success_page_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );

        if (!$message) {
            $message = __('Your payment is being confirmed.');
        }

        $page->getConfig()->getTitle()->set($message);

        return $page;
    }
}
