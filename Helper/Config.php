<?php
/**
 * ZPay Payment Gateway
 *
 * @category ZPay
 * @package ZPay\Standard
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link https://github.com/tiagosampaio
 * @link https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use ZPay\Standard\Model\Config\Source\Environment;

/**
 * Class Config
 *
 * @package ZPay\Standard\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @return array
     */
    public function getServiceConfiguration()
    {
        return [
            'username'    => $this->getUsername(),
            'password'    => $this->getPassword(),
            'contract_id' => $this->getContractId(),
            'environment' => $this->getEnvironment(),
        ];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return (string) $this->scopeConfig->getValue('payment/zpay_standard/username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return (string) $this->scopeConfig->getValue('payment/zpay_standard/password');
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return (string) $this->scopeConfig->getValue('payment/zpay_standard/environment');
    }

    /**
     * @return string
     */
    public function getContractId()
    {
        return (string) $this->scopeConfig->getValue('payment/zpay_standard/contract_id');
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return (string) $this->_getUrl('zpay/standard/callback', ['ajax' => true]);
    }

    /**
     * @param null|string $environment
     *
     * @return string
     */
    public function getServiceUrl($environment = null)
    {
        if (empty($environment)) {
            $environment = Environment::PRODUCTION;
        }

        return (string) $this->scopeConfig->getValue("payment/zpay_standard/service_url_{$environment}");
    }
}
