<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
namespace ZPay\Standard\Block\Info;

use Magento\Payment\Block\Info;

class Standard extends Info
{
    
    /**
     * @var string
     */
    protected $_template = 'ZPay_Standard::info/standard.phtml';
    
}
