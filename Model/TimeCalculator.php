<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 * @link   https://github.com/tiagosampaio
 * @link   https://tiagosampaio.com
 *
 * Copyright (c) 2019.
 */

declare(strict_types = 1);

namespace ZPay\Standard\Model;

/**
 * Class TimeCalculator
 * @package ZPay\Standard\Model
 */
class TimeCalculator
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * @param string $timestamp
     * @param int    $milliseconds
     * @return \DateTime
     * @throws \Exception
     */
    public function calculate($timestamp, $milliseconds)
    {
        $timestamp = $this->convertDatetime($timestamp);
        $seconds   = abs(max($milliseconds, 1000)/1000);
        $dateInterval = new \DateInterval("PT{$seconds}S");
        $timestamp->add($dateInterval);

        return $timestamp;
    }

    /**
     * @param string $timestamp
     * @return \DateTime
     */
    private function convertDatetime($timestamp)
    {
        return $this->timezone->date(strtotime($timestamp));
    }
}
