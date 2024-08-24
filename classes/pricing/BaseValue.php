<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing;

abstract class BaseValue
{
    /**
     * String-Representation of this class instance.
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Return integer-formatted value.
     * @return integer
     */
    abstract public function toInt(): int;

    /**
     * Return floated-formatted value.
     * @return float
     */
    abstract public function toFloat(): float;
}
