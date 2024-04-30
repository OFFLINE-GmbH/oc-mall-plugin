<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing;

abstract class BaseValue
{
    /**
     * String-Representation of this class instance.
     * @return string
     */
    abstract function __toString(): string;

    /**
     * Return integer-formatted value.
     * @return integer
     */
    abstract function toInt(): int;

    /**
     * Return floated-formatted value.
     * @return float
     */
    abstract function toFloat(): float;
}
