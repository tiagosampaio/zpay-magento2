<?php

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use ZPay\Standard\Model\Transaction\Order;

class Update extends PaymentAbstract
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Order $order */
        $order = $this->getZPayOrder();

        if (!$order || !$order->getId()) {
            return false;
        }

        /** @var bool|\stdClass $result */
        $data = $this->api->renewOrder($order->getZpayOrderId());

        if (!$this->validate($data)) {
            return false;
        }

        try {
            $order->setZpayOrderId((string) $data->order_id)
                ->setZpayQuoteId((string) $data->quote_id)
                ->setZpayAmountTo((float) $data->amount_to)
                ->setZpayAddress((string) $data->address)
                ->setZpayTime((int) $data->time)
                ->setZpayTimestamp($data->timestamp)
                ->save();

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData((array) $data);
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
