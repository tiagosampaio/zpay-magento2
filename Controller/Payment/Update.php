<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Update
 *
 * @package ZPay\Standard\Controller\Payment
 */
class Update extends PaymentAbstract
{
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
            ;
            
            $this->transactionOrderRepository->save($order);
            
            /** @var \Magento\Sales\Model\Order $salesOrder */
            $salesOrder = $this->orderRepository->get($order->getOrderId());
            
            $grandTotal = (float) $salesOrder->getGrandTotal();
            $bitcoinRate = (float) ($grandTotal / $order->getZpayAmountTo());
            
            $data->total_brl = $this->helperPricing->currency($grandTotal, true, false);
            $data->rate = $this->helperPricing->currency($bitcoinRate, true, false);
            
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
