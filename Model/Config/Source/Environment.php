<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

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
