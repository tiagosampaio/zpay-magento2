<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */
namespace ZPay\Standard\Block\Form;

use Magento\Payment\Block\Form;

class Standard extends Form
{
    /**
     * Checkmo template
     *
     * @var string
     */
    protected $_template = 'ZPay_Standard::form/standard.phtml';
    
}
