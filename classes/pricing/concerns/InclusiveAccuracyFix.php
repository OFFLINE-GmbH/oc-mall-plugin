<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\Values\DiscountValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\MoneyValue;
use Whitecube\Price\Price;

/**
 * Whitecube\Price\Price works exclusively with exclusive prices. As a result, when inclusive prices
 * are used, there may be rounding discrepancies, typically less than 1 cent, due to the deduction
 * of tax percentages. However, this trait ensures accurate display by effectively addressing and
 * resolving such rounding issues using the used currency precision (or 0.001).
 */
trait InclusiveAccuracyFix
{
    /**
     * Clean exclusive, by getting rid of those taxes.
     * @param Price $price
     * @return Price
     */
    protected function cleanExclusive(Price $price): Price
    {
        if (!$this->priceInclusive) {
            return $price;
        }

        // Summarize tax-factors and amounts
        $factor = 0;
        $amount = Money::ofMinor('0', $this->currency);
        
        // VAT
        if ($this->vat && $this->vat->value() > 0) {
            $factor += $this->vat->value();
        }

        // Taxes
        foreach ($this->taxes as $tax) {
            if ($tax instanceof FactorValue) {
                $factor += $tax->value();
            } else {
                $amount->plus($tax->base());
            }
        }

        // Subtract Taxes
        if ($factor > 0) {
            $price->dividedBy(1 + ($factor / 100), RoundingMode::HALF_UP);
        }

        if ($amount->getMinorAmount()->toInt() > 0) {
            $price->minus($amount);
        }

        // Return Price
        return $price;
    }

    /**
     * Discounts.
     * @param Money $money
     * @return Money
     */
    protected function cleanDiscount(Money $money): Money
    {
        if (!$this->priceInclusive) {
            return $money;
        }
        
        // Collect taxes
        $factor = 0;
        $amount = Money::ofMinor('0', $this->currency);

        if ($this->vat && $this->vat->value() > 0) {
            $factor += $this->vat->value();
        }

        foreach ($this->taxes as $tax) {
            if ($tax instanceof MoneyValue) {
                $amount->plus($tax->value());
            } else {
                $factor += $tax->value();
            }
        }

        // Subtract tax amount of discount
        if ($factor > 0) {
            $money = $money->dividedBy(1 + ($factor / 100), RoundingMode::HALF_UP);
        }

        if ($amount->getMinorAmount()->toInt() > 0) {
            $money = $money->minus($amount);
        }

        return $money;
    }

    /**
     * Return summarized discount value.
     * @return int|float
     */
    protected function sumDiscount()
    {
        $price = $this->price->inclusive();

        return array_reduce(
            $this->discounts,
            function ($carry, DiscountValue $discount) use ($price) {
                $amount = $discount->value();

                if ($amount instanceof MoneyValue) {
                    $carry += $amount->toFloat();
                } else {
                    $carry += $amount->valueOf($price)->getAmount()->toFloat();
                }

                return $carry;
            },
            0
        );
    }

    /**
     * Clean inclusive, without gross-rounding issues.
     * @param Price $price
     * @return Price
     */
    protected function cleanInclusive(Price $price): Price
    {
        if (!$this->priceInclusive) {
            return $price;
        }

        // Get amounts
        $original = $this->price->inclusive()->getAmount()->toFloat();
        $original -= $this->sumDiscount();

        /** @var Money */
        $inclusive = $price->inclusive();
        $result = $inclusive->getAmount()->toFloat();

        // Handle rounding issues
        $diff = abs($original - $result);

        if (($currency = $this->bag()->getCurrency()) !== null) {
            $diff = intval($diff * ($p = pow(10, $currency->decimals))) / $p;
            $accuracy = floatval('0.' . substr(str_repeat('0', intval($currency->decimals)), -1) . '1');

            if ($diff <= $accuracy) {
                $price = new Price(Money::ofMinor($original * 100, $this->currency));
            }
        } else {
            $diff = intval($diff * ($p = pow(10, 3))) / $p;

            if ($diff <= 0.001) {
                $price = new Price(Money::ofMinor($original * 100, $this->currency));
            }
        }

        // Return fixed price.
        return $price;
    }
}
