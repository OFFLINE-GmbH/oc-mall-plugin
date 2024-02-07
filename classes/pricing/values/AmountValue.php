<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class AmountValue extends BaseValue
{
    /**
     * Full amount.
     * @var Price
     */
    protected Price $amount;

    /**
     * Create a new AmountValue instance.
     * @param Price $amount
     */
    public function __construct(Price $amount)
    {
        $this->amount = $amount;
    }

    /**
     * String-Representation of the price value instance.
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->amount);
    }

    /**
     * Return amount value.
     * @return Price
     */
    public function value()
    {
        return $this->amount;
    }

    /**
     * Return base abstract money instance of price value.
     * @return Money
     */
    public function base(): Money
    {
        return $this->amount->base();
    }
}
