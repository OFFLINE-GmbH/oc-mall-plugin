<?php

namespace OFFLINE\Mall\Classes\Totals;

use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;

class ShippingTotal
{
    /**
     * @var TotalsCalculator
     */
    private $totals;
    /**
     * @var ShippingMethod
     */
    private $method;
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $price;
    /**
     * @var int
     */
    private $taxes;

    public function __construct(?ShippingMethod $method, TotalsCalculator $totals)
    {
        $this->method = $method;
        $this->totals = $totals;

        $this->calculate();
    }

    protected function calculate()
    {
        $this->price = $this->calculatePrice();
        $this->taxes = $this->calculateTaxes();
        $this->total = $this->calculateTotal();
    }

    protected function calculateTotal()
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->price;

        return $price - $this->taxes;
    }

    protected function calculateTaxes(): int
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->price;

        return $this->method->taxes->reduce(function ($total, Tax $tax) use ($price) {
            return $total += $tax->percentageDecimal * $price;
        }, 0);
    }

    protected function calculatePrice(): int
    {
        if ( ! $this->method) {
            return 0;
        }

        $method = $this->method;
        $price  = $method->getOriginal('price');

        // If there are special rates let's see if they
        // need to be applied.
        if ($method->rates->count() > 0) {
            $weight = $this->totals->weightTotal();

            $matchingRate = $method->rates->first(function (ShippingMethodRate $rate) use ($weight) {
                $compareFrom = $rate->from_weight === null || $rate->from_weight <= $weight;
                $compareTo   = $rate->to_weight === null || $rate->to_weight >= $weight;

                return $compareFrom && $compareTo;
            });

            if ($matchingRate) {
                $price = $matchingRate->getOriginal('price');
            }
        }

        $price = $this->applyDiscounts($price);

        return $price > 0 ? $price : 0;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function taxes(): int
    {
        return $this->taxes;
    }

    public function total(): int
    {
        return $this->total;
    }

    private function applyDiscounts(int $price): int
    {
        $discount = $this->totals->getCart()->discounts->where('type', 'shipping')->first();
        if ( ! $discount) {
            return $price;
        }

        $applier = new DiscountApplier($this->totals->getCart(), $this->totals->productTotal(), $price);
        $applier->apply($discount);

        return $applier->reducedTotal();
    }
}
