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

namespace ZPay\Standard\Exception;

use Magento\Framework\Phrase;

/**
 * Class LocalizedException
 *
 * @package ZPay\Standard\Exception
 */
class LocalizedException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @var int
     */
    private $httpCode;

    /**
     * LocalizedException constructor.
     *
     * @param Phrase          $phrase
     * @param int             $httpCode
     * @param \Exception|null $cause
     * @param int             $code
     */
    public function __construct(Phrase $phrase, $httpCode = 200, \Exception $cause = null, $code = 0)
    {
        $this->setHttpCode($httpCode);
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @param int $httpCode
     */
    public function setHttpCode($httpCode)
    {
        if (!$httpCode) {
            return;
        }

        $this->httpCode = (int) $httpCode;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return (int) $this->httpCode;
    }
}
