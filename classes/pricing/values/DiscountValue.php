<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class DiscountValue extends BaseValue
{
    /**
     * The desired value instance.
     * @var AmountValue|FactorValue
     */
    protected AmountValue|FactorValue $value;

    /**
     * Whether the value applies per unit or on total.
     * @var boolean
     */
    protected bool $perUnit = false;

    /**
     * Create a new DiscountValue instance.
     * @param AmountValue|FactorValue $value
     * @param bool $perUnit
     */
    public function __construct(AmountValue|FactorValue $value, bool $perUnit = false)
    {
        $this->value = $value;
        $this->perUnit = $perUnit;
    }

    /**
     * String-Representation of this class instance.
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->value);
    }

    /**
     * Return raw value.
     * @return AmountValue|FactorValue
     */
    public function value(): AmountValue|FactorValue
    {
        return $this->value;
    }

    /**
     * Whether the value applies per unit or on total.
     * @return boolean
     */
    public function isPerUnit(): bool
    {
        return $this->perUnit;
    }
}
