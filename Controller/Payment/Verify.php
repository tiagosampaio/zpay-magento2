<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use ZPay\Standard\Model\Transaction\Order;

/**
 * Class Verify
 *
 * @package ZPay\Standard\Controller\Payment
 */
class Verify extends PaymentAbstract
{
    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     *
     * @throws \ZPay\Standard\Exception\ServiceApiResponseException
     */
    public function execute()
    {
        /** @var Order $order */
        $order = $this->getZPayOrder();
        
        if (!$order || !$order->getId()) {
            return false;
        }
        
        /** @var \stdClass $status */
        $object = $this->api->getOrderStatus($order->getZpayOrderId());
        
        if (!$this->validate($object)) {
            return false;
        }
        
        $data = [];
        
        try {
            $paymentStatus = (string) $object->payment_status;
            
            if ($this->statusVerification->isPaid($paymentStatus)) {
                $data['status'] = $paymentStatus;
                $this->storage->setData(self::CONFIRMED_ORDER_ID_KEY, $order->getZpayOrderId());
            }
            
            if (!$this->statusVerification->isPaid($paymentStatus)) {
                $data['status'] = $paymentStatus;
            }
        } catch (\Exception $e) {
        }
        
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData((array) $data);
    }
    
    /**
     * @param \stdClass $object
     *
     * @return bool
     */
    protected function validate($object)
    {
        if (!parent::validate($object)) {
            return false;
        }
        
        try {
            if (!$object->contract_id) {
                return false;
            }
            
            if (!$object->merchant_id) {
                return false;
            }
            
            if (!$object->order_status) {
                return false;
            }
            
            if (!$object->payment_status) {
                return false;
            }
            
            if (!$object->original_price) {
                return false;
            }
            
            if (!$object->reference_id) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
}
