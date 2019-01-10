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

namespace ZPay\Standard\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Environment
 *
 * @package ZPay\Standard\Model\Config\Source
 */
class Environment implements ArrayInterface
{
    /**
     * @var string
     */
    const PRODUCTION = 'production';
    /**
     * @var string
     */
    const SANDBOX = 'sandbox';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SANDBOX    => __('Sandbox'),
            self::PRODUCTION => __('Production')
        ];
    }
}
