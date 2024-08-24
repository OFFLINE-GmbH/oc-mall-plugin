<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class FactorValue extends BaseValue
{
    /**
     * The desired factor value (between 0 and 1).
     * @var float
     */
    protected float $factor;

    /**
     * The desired factor value as percentage (between 0 and 100).
     * @var float|int
     */
    protected $percentage;

    /**
     * Create a new FactorValue instance.
     * @param integer|float $value
     */
    public function __construct($value)
    {
        if (is_float($value) && $value >= 0 && $value <= 1) {
            $this->factor = $value;
            $this->percentage = $value * 100;
        } else {
            $this->factor = $value / 100;
            $this->percentage = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return strval($this->percentage . '%');
    }

    /**
     * Return factor value.
     * @return int|float
     */
    public function value()
    {
        return $this->percentage;
    }

    /**
     * Calculate and return the factor value of the passed price.
     * @param Money|Price $amount
     * @param bool $perUnit Whether to return unit or total price, used only  when Price object is
     *                      passed.
     * @return Money
     */
    public function valueOf($amount, bool $perUnit = false): Money
    {
        if ($amount instanceof Price) {
            $amount = clone $amount->base($perUnit);
        }
        /** @var Money $amount */

        $percentage = BigDecimal::of($this->percentage);
        $multiplier = $percentage->dividedBy(100, $percentage->getScale() + 2, RoundingMode::UP);

        return $amount->multipliedBy($multiplier, RoundingMode::HALF_UP);
    }

    /**
     * @inheritDoc
     */
    public function toInt(): int
    {
        return $this->percentage;
    }

    /**
     * @inheritDoc
     */
    public function toFloat(): float
    {
        return $this->factor;
    }
}
