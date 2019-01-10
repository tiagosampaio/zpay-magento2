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
 * Class AbstractException
 *
 * @package ZPay\Standard\Exception
 */
abstract class AbstractException extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase
     */
    protected $phrase;

    /**
     * @var string
     */
    protected $logMessage;

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception                $cause
     * @param int                       $code
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), intval($code), $cause);
    }
}
