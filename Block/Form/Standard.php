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

namespace ZPay\Standard\Block\Form;

use Magento\Payment\Block\Form;

/**
 * Class Standard
 *
 * @package ZPay\Standard\Block\Form
 */
class Standard extends Form
{
    /**
     * @var string
     */
    protected $_template = 'ZPay_Standard::form/standard.phtml';
}
