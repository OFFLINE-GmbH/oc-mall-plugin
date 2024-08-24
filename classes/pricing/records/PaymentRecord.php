<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use Whitecube\Price\Price;

class PaymentRecord extends AbstractItemRecord
{
    public const TYPE = 'payment';

    /**
     * Percentage fee to be added to the totals.
     * @var integer|float
     */
    protected $percentage = 0;

    /**
     * Create a new payment record.
     * @param string $currency
     * @param null|int|float $percentage Percentage fee.
     * @param null|int|float|string|Price Fixed amount.
     * @param null|mixed $amount
     */
    public function __construct(string $currency, $percentage = null, $amount = null)
    {
        if ($amount instanceof Price) {
            $amount->setUnits(1);
        } elseif (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $currency, 1);
            } else {
                $amount = new Price(Money::ofMinor($amount ?? 0, $currency), 1);
            }
        }

        $this->currency = $currency;
        $this->price = $amount;
        $this->percentage = $percentage ?? 0;
        $this->priceInclusive = false;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type'          => $this->type(),
            'exclusive'     => strval($this->exclusive()),
            'discounts'     => strval($this->discount()),
            'percentage'    => $this->percentage,
        ];
    }

    /**
     * @inheritDoc
     */
    public function setVat($value): self
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }

    /**
     * @inheritDoc
     */
    public function factor()
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }

    /**
     * @inheritDoc
     */
    public function vat(): Money
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }

    /**
     * @inheritDoc
     */
    public function tax(bool $excludeVat = false): Money
    {
        throw new PriceBagException('The PaymentRecord class does not support the inclusive method, please use taxFromTotals instead.');
    }

    /**
     * @inheritDoc
     */
    public function inclusive(): PriceValue
    {
        throw new PriceBagException('The PaymentRecord class does not support the inclusive method, please use inclusiveFromTotals instead.');
    }

    /**
     * Return exclusive price value using fixed amount and percentage fee.
     * @param Money $money
     * @return void
     */
    public function exclusiveFromTotals(Money $money): PriceValue
    {
        $price = new Price($this->exclusive()->exclusive());

        // Add fee
        if ($this->percentage > 0) {
            $percentage = BigDecimal::of($this->percentage);
            $multiplier = $percentage->dividedBy(100, $percentage->getScale() + 2, RoundingMode::UP);
            $price->plus($money->multipliedBy($multiplier, RoundingMode::HALF_UP));
        }

        return new PriceValue($price);
    }

    /**
     * Return sum of all taxes based on the original net-price minus discount, calculated from the
     * total price.
     * @param Money $money
     * @return Money
     */
    public function taxFromTotals(Money $money): Money
    {
        $original = new Price($this->exclusive()->exclusive());

        // Add fee
        if ($this->percentage > 0) {
            $percentage = BigDecimal::of($this->percentage);
            $multiplier = $percentage->dividedBy(100, $percentage->getScale() + 2, RoundingMode::UP);
            $original->plus($money->multipliedBy($multiplier, RoundingMode::HALF_UP));
        }

        // Remove Discount
        $original->minus($this->discount());

        // Add Taxes
        $price = Price::parse('0', $this->currency);

        foreach ($this->taxes as $tax) {
            if ($tax instanceof FactorValue) {
                $value = $tax->valueOf($original);
            } else {
                $value = $tax->value();
            }
            $price->plus($value);
        }

        return $price->base();
    }

    /**
     * Return inclusive price value using fixed amount and percentage fee, containing discounts, vat
     * and other taxes.
     * @param Money $money
     * @return void
     */
    public function inclusiveFromTotals(Money $money): PriceValue
    {
        $price = new Price($this->exclusive()->exclusive());

        // Add fee
        if ($this->percentage > 0) {
            $percentage = BigDecimal::of($this->percentage);
            $multiplier = $percentage->dividedBy(100, $percentage->getScale() + 2, RoundingMode::UP);
            $price->plus($money->multipliedBy($multiplier, RoundingMode::HALF_UP));
        }

        // Remove Discount
        $price->minus($this->discount());

        // Add Taxes
        $original = clone $price;

        foreach ($this->taxes as $tax) {
            if ($tax instanceof FactorValue) {
                $value = $tax->valueOf($original);
            } else {
                $value = $tax->value();
            }
            $price->plus($value);
        }

        return new PriceValue($price);
    }

    /**
     * Return record type
     * @return string
     */
    protected function type(): string
    {
        return self::TYPE;
    }
}
