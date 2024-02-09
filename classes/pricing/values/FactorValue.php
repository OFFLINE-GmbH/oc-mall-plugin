<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class FactorValue extends BaseValue
{
    /**
     * Plain Factor Value.
     * @var integer|float
     */
    protected int|float $factor;

    /**
     * Create a new FactorValue instance.
     * @param integer|float $factor
     */
    public function __construct(int|float $factor)
    {
        $this->factor = $factor;
    }

    /**
     * String-Representation of this class instance.
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->factor . '%');
    }

    /**
     * Return factor value.
     * @return int|float
     */
    public function value()
    {
        return $this->factor;
    }

    /**
     * Calculate and return the factor value of the passed price
     * @param Money|Price $amount
     * @return Money
     */
    public function valueOf(Money|Price $amount): Money
    {
        if ($amount instanceof Money) {
            $amount = new Price($amount);
        }

        $price = clone $amount;
        $price->setVat($this->factor);
        return $price->vat()->money();
    }
}
