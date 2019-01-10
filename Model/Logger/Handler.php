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

namespace ZPay\Standard\Model\Logger;

/**
 * Class Handler
 *
 * @package ZPay\Standard\Model\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     */
    const CONFIG_LOGGER_XPATH = 'payment/zpay_standard/logs_enabled';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = 'var/log/zpay_standard_request.log';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        if (!$this->isLogEnabled()) {
            return false;
        }

        return parent::isHandling($record);
    }

    /**
     * @return bool
     */
    private function isLogEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_LOGGER_XPATH);
    }
}
