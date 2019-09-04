<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;


use October\Rain\Support\Collection;
use OFFLINE\Mall\Models\Tax;

trait CartItemPriceAccessors
{

    /**
     * The total item price * quantity post taxes.
     * @return float
     */
    public function getTotalPostTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer * $this->quantity;
        }

        return $this->totalPreTaxes + $this->totalTaxes;
    }

    /**
     * The total quantity for this cart entry.
     * @return float
     */
    public function getTotalTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            $withoutTax = 1 / (1 + $this->taxFactor()) * $this->price()->integer * $this->quantity;

            return $this->price()->integer * $this->quantity - $withoutTax;
        }

        return $this->taxFactor() * $this->price()->integer * $this->quantity;
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->weight * $this->quantity;
    }

    public function getPricePostTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer;
        }

        return $this->price()->integer + $this->price()->integer * $this->taxFactor();
    }

    public function totalForTax(Tax $tax)
    {
        return $tax->percentageDecimal * $this->getTotalPreTaxesAttribute();
    }

    /**
     * Sum of all tax factors.
     * @return mixed
     */
    protected function taxFactor()
    {
        return $this->filtered_taxes->sum('percentageDecimal');
    }

    /**
     * The total item price * quantity pre taxes.
     * @return float
     */
    public function getTotalPreTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer * $this->quantity - $this->totalTaxes;
        }

        return $this->price()->integer * $this->quantity;
    }

    /**
     * Filter taxes by shipping destination.
     *
     * @return Collection
     */
    public function getFilteredTaxesAttribute()
    {
        $taxes = optional($this->data)->taxes ?? new Collection();

        return $taxes->filter(function (Tax $tax) {
            // If no shipping address is available only include taxes that have no country restrictions.
            if ($this->cart->shipping_address === null) {
                return $tax->countries->count() === 0;
            }

            return $tax->countries->count() === 0
                || $tax->countries->pluck('id')->search($this->cart->shipping_address->country_id) !== false;
        });
    }

    public function getPricePreTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return 1 / (1 + $this->taxFactor()) * $this->price()->integer;
        }

        return $this->price()->integer;
    }
}