<?php

namespace OFFLINE\Mall\Models;


use October\Rain\Database\Pivot;

class CartProduct extends Pivot
{
    public $casts = [
        'quantity' => 'integer',
        'price'    => 'integer',
    ];

    public $belongsTo = [
        'product' => Product::class,
    ];

    public function getTotalPreTaxesAttribute(): int
    {
        if ($this->product->price_includes_tax) {
            return $this->price * $this->quantity - $this->totalTaxes;
        }

        return $this->price * $this->quantity;
    }

    public function getTotalTaxesAttribute(): int
    {
        if ($this->product->price_includes_tax) {
            $withoutTax = $this->priceWithoutTaxes();

            return $this->price * $this->quantity - $withoutTax;
        }

        return $this->taxFactor() * $this->price * $this->quantity;
    }

    public function getTotalPostTaxesAttribute(): int
    {
        if ($this->product->price_includes_tax) {
            return $this->price * $this->quantity;
        }

        return $this->totalPreTaxes + $this->totalTaxes;
    }

    public function getWeightAttribute(): int
    {
        return $this->product->weight * $this->quantity;
    }

    protected function priceWithoutTaxes()
    {
        if ($this->product->price_includes_tax) {
            return 1 / (1 + $this->taxFactor()) * $this->price * $this->quantity;
        }

        return $this->price * $this->quantity;
    }

    public function totalForTax(Tax $tax)
    {
        return $tax->percentageDecimal * $this->priceWithoutTaxes();
    }

    /**
     * Sum of all tax factors.
     * @return mixed
     */
    protected function taxFactor()
    {
        return $this->product->taxes->reduce(function ($total, Tax $tax) {
            return $total += $tax->percentageDecimal;
        });
    }

}