<?php

namespace OFFLINE\Mall\Models;

use Model;

class CartProduct extends Model
{
    public $table = 'offline_mall_cart_products';
    public $casts = [
        'quantity' => 'integer',
        'price'    => 'integer',
    ];

    public $belongsTo = [
        'cart'    => Cart::class,
        'product' => Product::class,
        'data'    => [Product::class, 'key' => 'product_id'],
    ];

    public $hasMany = [
        'custom_field_values' => [CustomFieldValue::class, 'key' => 'cart_product_id', 'otherKey' => 'id'],
    ];

    public function getTotalPreTaxesAttribute(): int
    {
        if ($this->data->price_includes_tax) {
            return $this->price * $this->quantity - $this->totalTaxes;
        }


        return $this->price * $this->quantity;
    }

    public function getTotalTaxesAttribute(): int
    {
        if ($this->data->price_includes_tax) {
            $withoutTax = $this->priceWithoutTaxes();

            return $this->price * $this->quantity - $withoutTax;
        }

        return $this->taxFactor() * $this->price * $this->quantity;
    }

    public function getTotalPostTaxesAttribute(): int
    {
        if ($this->data->price_includes_tax) {
            return $this->price * $this->quantity;
        }

        return $this->totalPreTaxes + $this->totalTaxes;
    }

    public function getWeightAttribute(): int
    {
        return $this->data->weight * $this->quantity;
    }

    protected function priceWithoutTaxes()
    {
        if ($this->data->price_includes_tax) {
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
        return $this->data->taxes->reduce(function ($total, Tax $tax) {
            return $total += $tax->percentageDecimal;
        });
    }
}
