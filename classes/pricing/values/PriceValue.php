<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class PriceValue extends BaseValue
{
    /**
     * The desired price instance.
     * @var Price
     */
    protected Price $price;

    /**
     * Create a new PriceValue instance.
     * @param Money|Price $price
     */
    public function __construct($price)
    {
        if ($price instanceof Money) {
            $price = new Price($price);
        }
        $this->price = $price;
    }

    /**
     * Cloned class object should have his own cloned price instance.
     * @return void
     */
    public function __clone()
    {
        $this->price = clone $this->price;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return strval($this->price);
    }

    /**
     * Return copy of the internal money object.
     * @param bool $perUnit Whether to return unit or total price.
     * @return Money
     */
    public function base(bool $perUnit = false): Money
    {
        return clone $this->price->base($perUnit);
    }

    /**
     * Return copy of the internal price object.
     * @param bool $perUnit Whether to return unit or total price.
     * @return Price
     */
    public function value(bool $perUnit = false): Price
    {
        $price = clone $this->price;

        if ($perUnit) {
            $price->setUnits(1);
        }

        return $price;
    }

    /**
     * Return copy of the internal price object, using the exclusive price.
     * @param bool $perUnit Whether to return unit or total price.
     * @return Money
     */
    public function exclusive(bool $perUnit = false): Money
    {
        return clone $this->price->exclusive($perUnit);
    }

    /**
     * Return copy of the internal price object, using the inclusive price.
     * @param bool $perUnit Whether to return unit or total price.
     * @return Money
     */
    public function inclusive(bool $perUnit = false): Money
    {
        return clone $this->price->inclusive($perUnit);
    }

    /**
     * @inheritDoc
     */
    public function toInt(): int
    {
        return $this->base(false)->getMinorAmount()->toInt();
    }

    /**
     * @inheritDoc
     */
    public function toFloat(): float
    {
        return $this->base(false)->getAmount()->toFloat();
    }
}
