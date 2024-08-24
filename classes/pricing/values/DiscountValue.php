<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use OFFLINE\Mall\Classes\Pricing\BaseValue;

class DiscountValue extends BaseValue
{
    /**
     * The desired value instance.
     * @var FactorValue|MoneyValue
     */
    protected $value;

    /**
     * Whether the value applies per unit or on the total price.
     * @var boolean
     */
    protected bool $perUnit = false;

    /**
     * Create a new DiscountValue instance.
     * @param FactorValue|MoneyValue $value
     * @param bool $perUnit
     */
    public function __construct($value, bool $perUnit = false)
    {
        $this->value = $value;
        $this->perUnit = $perUnit;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return strval($this->value);
    }

    /**
     * Return copy of the internal value object.
     * @return FactorValue|MoneyValue
     */
    public function value()
    {
        return clone $this->value;
    }

    /**
     * Whether the value applies per unit or on the total price.
     * @return boolean
     */
    public function isPerUnit(): bool
    {
        return $this->perUnit;
    }

    /**
     * @inheritDoc
     */
    public function toInt(): int
    {
        return $this->value->toInt();
    }

    /**
     * @inheritDoc
     */
    public function toFloat(): float
    {
        return $this->value->toFloat();
    }
}
