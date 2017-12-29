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
    private $preTaxes;
    /**
     * @var int
     */
    private $total;
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
        $this->total    = $this->calculateTotal();
        $this->taxes    = $this->calculateTaxes();
        $this->preTaxes = $this->calculatePreTax();
    }

    protected function calculatePreTax()
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->total;

        return $price - $this->taxes;
    }

    protected function calculateTaxes(): int
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->total;

        return $this->method->taxes->reduce(function ($total, Tax $tax) use ($price) {
            return $total += $price / (1 + $tax->percentageDecimal) * $tax->percentageDecimal;
        }, 0);
    }

    protected function calculateTotal(): int
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

    public function totalPreTaxes(): int
    {
        return $this->preTaxes;
    }

    public function totalTaxes(): int
    {
        return $this->taxes;
    }

    public function totalPostTaxes(): int
    {
        return $this->total;
    }

    private function applyDiscounts(int $price): int
    {
        $discount = $this->totals->getCart()->discounts->where('type', 'shipping')->first();
        if ( ! $discount) {
            return $price;
        }

        $applier = new DiscountApplier($this->totals->getCart(), $this->totals->productPostTaxes(), $price);
        $applier->apply($discount);

        return $applier->reducedTotal();
    }
}
