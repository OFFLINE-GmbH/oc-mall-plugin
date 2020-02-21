<?php

namespace OFFLINE\Mall\Classes\Totals;

use Carbon\Carbon;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;

class ShippingTotal implements \JsonSerializable
{
    use Rounding;
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
    /**
     * @var int
     */
    private $appliedDiscount;
    /**
     * @var int
     */
    protected $price;

    public function __construct(?ShippingMethod $method, TotalsCalculator $totals)
    {
        $this->method = $method;
        $this->totals = $totals;

        $this->calculate();
    }

    protected function calculate()
    {
        $this->taxes    = $this->calculateTaxes();
        $this->preTaxes = $this->calculatePreTax();
        $this->total    = $this->calculateTotal();
    }

    protected function calculatePreTax()
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->getPrice();

        if ($this->method->price_includes_tax) {
            return $price - $this->taxes;
        }

        return $price;
    }

    protected function calculateTaxes(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->getPrice();

        $totalTaxPercentage = $this->totals->shippingTaxes->sum('percentageDecimal');

        $totalTax = $this->totals->shippingTaxes->sum(function (Tax $tax) use ($price, $totalTaxPercentage) {
            if ($this->method->price_includes_tax) {
                return $price / (1 + $totalTaxPercentage) * $tax->percentageDecimal;
            }

            return $price * $tax->percentageDecimal;
        });

        return $this->round($totalTax);
    }

    protected function calculateTotal(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->getPrice();

        if ($this->method->price_includes_tax === false) {
            $price += $this->taxes;
        }

        return $price > 0 ? $price : 0;
    }

    public function totalPreTaxes(): float
    {
        return $this->preTaxes;
    }

    public function totalPreTaxesOriginal(): float
    {
        return $this->preTaxes;
    }

    public function totalTaxes(): float
    {
        return $this->taxes;
    }

    public function totalPostTaxes(): float
    {
        return $this->total;
    }

    public function appliedDiscount()
    {
        return $this->appliedDiscount;
    }

    /**
     * Get the effective ShippingMethod including changes
     * made by any applied discounts.
     *
     * @return ?ShippingMethod
     */
    public function method(): ?ShippingMethod
    {
        if ($this->totals->getInput()->products->every('data.is_virtual')) {
            return ShippingMethod::noShippingRequired();
        }

        if ( ! $this->appliedDiscount) {
            return $this->method;
        }

        $method = $this->method->replicate(['id', 'name']);

        $discount     = $this->appliedDiscount['discount'];
        $method->name = $discount->shipping_description;
        $method->setRelation('prices', $discount->shipping_prices);

        return $method;
    }

    private function applyDiscounts(int $price): ?float
    {
        $discounts = Discount::whereIn('trigger', ['total', 'product'])
            ->where('type', 'shipping')
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', Carbon::now());
            })->where(function ($q) {
                $q->whereNull('expires')
                    ->orWhere('expires', '>', Carbon::now());
            })->get();

        $codeDiscount = $this->totals->getInput()->discounts->where('type', 'shipping')->first();
        if ($codeDiscount) {
            $discounts->push($codeDiscount);
        }

        if ($discounts->count() < 1) {
            return $price;
        }

        $applier = new DiscountApplier(
            $this->totals->getInput(),
            $this->totals->productPostTaxes(),
            $price
        );

        $this->appliedDiscount = optional($applier->applyMany($discounts))->first();

        return $applier->reducedTotal();
    }

    /**
     * @return mixed
     */
    protected function getPrice()
    {
        if ($this->price) {
            return $this->price;
        }

        $price = $this->method->price()->integer;

        // If there are special rates let's see if they need to be applied.
        if ($this->method->rates->count() > 0) {
            $weight       = $this->totals->weightTotal();
            $matchingRate = $this->method->rates->first(function (ShippingMethodRate $rate) use ($weight) {
                $compareFrom = $rate->from_weight === null || $rate->from_weight <= $weight;
                $compareTo   = $rate->to_weight === null || $rate->to_weight >= $weight;

                return $compareFrom && $compareTo;
            });

            if ($matchingRate) {
                $price = $matchingRate->price()->integer;
            }
        }

        if ($price) {
            $price = $this->applyDiscounts($price);
        }

        $this->price = $price;

        return $price;
    }

    public function jsonSerialize()
    {
        return [
            'method'          => $this->method(),
            'preTaxes'        => $this->preTaxes,
            'taxes'           => $this->taxes,
            'total'           => $this->total,
            'appliedDiscount' => $this->appliedDiscount,
        ];
    }

    public function __toString()
    {
        return (string)json_encode($this->jsonSerialize());
    }
}
