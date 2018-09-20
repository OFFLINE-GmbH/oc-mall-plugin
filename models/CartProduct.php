<?php

namespace OFFLINE\Mall\Models;

use DB;
use Model;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

class CartProduct extends Model
{
    use HashIds;
    use JsonPrice;

    public $table = 'offline_mall_cart_products';
    public $jsonable = ['price'];
    public $casts = [
        'quantity'   => 'integer',
        'id'         => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
    ];

    public $belongsTo = [
        'cart'    => Cart::class,
        'product' => Product::class,
        'variant' => Variant::class,
        'data'    => [Product::class, 'key' => 'product_id'],
    ];

    public $hasMany = [
        'custom_field_values' => [CustomFieldValue::class, 'key' => 'cart_product_id', 'otherKey' => 'id'],
    ];

    public $with = [
        'product',
        'product.taxes',
        'custom_field_values',
        'custom_field_values.custom_field',
        'custom_field_values.custom_field_option',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $cartProduct) {
            $cartProduct->quantity = $cartProduct->data->normalizeQuantity($cartProduct->quantity);
        });
        static::deleted(function (self $cartProduct) {
            CustomFieldValue::where('cart_product_id', $cartProduct->id)->delete();
        });
    }

    public function moveToOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            $this->reduceStock();

            $entry             = new OrderProduct();
            $entry->order_id   = $order->id;
            $entry->product_id = $this->product->id;
            $entry->variant_id = optional($this->variant)->id ?? null;

            $entry->item         = $this->item;
            $entry->name         = $this->data->name;
            $entry->variant_name = optional($this->variant)->properties_description;
            $entry->description  = $this->item->description;
            $entry->quantity     = $this->quantity;

            $entry->taxes = $this->item->taxes;

            // Set the attribute directly to prevent the price mutator from being triggered
            $entry->attributes['price_post_taxes'] = $this->priceInCurrencyInteger();
            $entry->attributes['price_taxes']      = $this->getTotalTaxesAttribute() / $this->quantity;
            $entry->attributes['price_pre_taxes']  = $this->pricePreTaxes();

            $entry->attributes['total_pre_taxes']  = $this->total_pre_taxes;
            $entry->attributes['total_taxes']      = $this->total_taxes;
            $entry->attributes['total_post_taxes'] = $this->total_post_taxes;

            $entry->tax_factor = $this->taxFactor();

            $entry->weight       = $this->weight;
            $entry->total_weight = $this->total_weight;

            $entry->width     = $this->item->width;
            $entry->length    = $this->item->length;
            $entry->height    = $this->item->height;
            $entry->stackable = $this->item->stackable;
            $entry->shippable = $this->item->shippable;

            if ($this->variant) {
                $entry->properties_description = $this->variant->propertyValuesAsString();
                $entry->property_values        = $this->variant->property_values;
            }

            $entry->custom_field_values = $this->convertCustomFieldValues();
            $entry->save();
        });
    }

    /**
     * Converts the custom field values into a simpler structure
     * to save it with the order.
     */
    public function convertCustomFieldValues()
    {
        return $this->custom_field_values
            ->load(['custom_field', 'custom_field_option'])
            ->map(function (CustomFieldValue $value) {
                $data                  = $value->toArray();
                $data['display_value'] = $value->displayValue;

                $prices = $value->priceForFieldOption($value->custom_field)->load('currency');

                $data['price'] = $prices->mapWithKeys(function (Price $price) {
                    return [$price->currency->code => $price->float];
                })->toArray();

                if (isset($data['custom_field']['custom_field_options'])) {
                    unset($data['custom_field']['custom_field_options']);
                }

                return $data;
            });
    }

    public function reduceStock()
    {
        return $this->item->reduceStock($this->quantity);
    }

    public function getItemAttribute()
    {
        return $this->variant ?? $this->product;
    }

    public function getTotalPreTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            return $this->priceInCurrencyInteger() * $this->quantity - $this->totalTaxes;
        }

        return $this->priceInCurrencyInteger() * $this->quantity;
    }

    public function getTotalTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            $withoutTax = $this->pricePreTaxes();

            return $this->priceInCurrencyInteger() * $this->quantity - $withoutTax;
        }

        return $this->taxFactor() * $this->priceInCurrencyInteger() * $this->quantity;
    }

    public function getTotalPostTaxesAttribute(): float
    {
        if ($this->data->price_includes_tax) {
            return $this->priceInCurrencyInteger() * $this->quantity;
        }

        return $this->totalPreTaxes + $this->totalTaxes;
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->weight * $this->quantity;
    }

    protected function pricePreTaxes()
    {
        if ($this->data->price_includes_tax) {
            return 1 / (1 + $this->taxFactor()) * $this->priceInCurrencyInteger() * $this->quantity;
        }

        return $this->priceInCurrencyInteger() * $this->quantity;
    }

    public function totalForTax(Tax $tax)
    {
        return $tax->percentageDecimal * $this->pricePreTaxes();
    }

    public function getCustomFieldValueDescriptionAttribute()
    {
        return $this->custom_field_values->map(function (CustomFieldValue $value) {
            return sprintf('%s: %s', e($value->custom_field->name), $value->display_value);
        })->implode('<br />');
    }

    /**
     * Sum of all tax factors.
     * @return mixed
     */
    protected function taxFactor()
    {
        return $this->data->taxes->sum('percentageDecimal');
    }
}
