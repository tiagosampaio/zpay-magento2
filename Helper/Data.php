<?php

namespace ZPay\Standard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{

    /**
     * @return string
     */
    public function getPaymentGatewayUrl()
    {
        return $this->_getUrl('zpay/payment/gateway');
    }


    /**
     * @return string
     */
    public function getPayUrl()
    {
        return $this->_getUrl('zpay/payment/pay');
    }


    /**
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->_getUrl('zpay/payment/update');
    }


    /**
     * @return string
     */
    public function getVerifyUrl()
    {
        return $this->_getUrl('zpay/payment/verify');
    }


    /**
     * @return string
     */
    public function getVerifiedUrl()
    {
        return $this->_getUrl('zpay/payment/verified');
    }


    /**
     * @return string
     */
    public function getCheckbackUrl()
    {
        return $this->_getUrl('zpay/standard/checkback');
    }
}
