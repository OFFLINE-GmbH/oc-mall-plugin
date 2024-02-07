<?php declare(strict_types=1);

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
     * @param Price $price
     */
    public function __construct(Price $price)
    {
        $this->price = $price;
    }

    /**
     * String-Representation of the desired price value instance.
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->price);
    }

    /**
     * Return amount value.
     * @return Price
     */
    public function value(): Price
    {
        return $this->price;
    }

    /**
     * Return exclusive amount value.
     * @return Money
     */
    public function exclusive(bool $perUnit = false): Money
    {
        return $this->price->exclusive($perUnit);
    }

    /**
     * Return inclusive amount value.
     * @return Money
     */
    public function inclusive(bool $perUnit = false): Money
    {
        return $this->price->inclusive($perUnit);
    }

    /**
     * Return base abstract money instance of price value..
     * @return Money
     */
    public function base(): Money
    {
        return $this->price->base();
    }

    /**
     * Return amount value as integer.
     * @return int
     */
    public function integer(): int
    {
        /** @var Money */
        $base = $this->price->base();
        return $base->getMinorAmount()->toInt();
    }

    /**
     * Return amount value as float.
     * @return float
     */
    public function float(): float
    {
        /** @var Money */
        $base = $this->price->base();
        return $base->getMinorAmount()->toFloat();
    }
}
