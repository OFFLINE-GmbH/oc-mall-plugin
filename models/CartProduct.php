<?php

namespace OFFLINE\Mall\Models;

use DB;
use Event;
use Model;
use OFFLINE\Mall\Classes\Traits\Cart\CartItemPriceAccessors;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

class CartProduct extends Model
{
    use HashIds;
    use JsonPrice;
    use CartItemPriceAccessors;

    public $table = 'offline_mall_cart_products';
    public $fillable = ['quantity', 'product_id', 'variant_id', 'weight', 'price'];
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
    public $belongsToMany = [
        'service_options' => [
            ServiceOption::class,
            'table'    => 'offline_mall_cart_product_service_option',
            'key'      => 'cart_product_id',
            'otherKey' => 'service_option_id',
        ],
    ];
    public $with = [
        'product',
        'product.taxes',
        'service_options.service.taxes',
        'service_options.prices',
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
        static::created(function (self $cartProduct) {
            Event::fire('mall.cart.product.added', [$cartProduct]);
        });
        static::updating(function (self $cartProduct) {
            Event::fire('mall.cart.product.updating', [$cartProduct]);
        });
        static::updated(function (self $cartProduct) {
            Event::fire('mall.cart.product.updated', [$cartProduct]);
        });
        static::deleted(function (self $cartProduct) {
            Event::fire('mall.cart.product.removed', [$cartProduct]);
            CustomFieldValue::where('cart_product_id', $cartProduct->id)->delete();
            DB::table('offline_mall_cart_product_service_option')->where('cart_product_id', $cartProduct->id)->delete();
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

            $entry->item         = $this->item_data;
            $entry->name         = $this->variant ? $this->variant->name : $this->data->name;
            $entry->variant_name = optional($this->variant)->properties_description;
            $entry->quantity     = $this->quantity;
            $entry->is_virtual   = $this->product->is_virtual;

            $entry->taxes           = $this->filtered_product_taxes;
            $entry->tax_factor      = $this->productTaxFactor();
            $entry->service_options = $this->service_options->toArray();

            // Set the attribute directly to prevent the price mutator from being triggered
            $entry->attributes['price_post_taxes'] = $this->price()->integer;
            $entry->attributes['price_taxes']      = $this->getTotalTaxesAttribute() / $this->quantity;
            $entry->attributes['price_pre_taxes']  = $this->getPricePreTaxesAttribute();

            $entry->attributes['total_pre_taxes']  = $this->total_pre_taxes;
            $entry->attributes['total_taxes']      = $this->total_taxes;
            $entry->attributes['total_post_taxes'] = $this->total_post_taxes;

            $entry->weight       = $this->weight;
            $entry->total_weight = $this->total_weight;

            $entry->width     = $this->item->width;
            $entry->length    = $this->item->length;
            $entry->height    = $this->item->height;
            $entry->stackable = $this->item->stackable;
            $entry->shippable = $this->item->shippable;
            $entry->brand     = $this->item->brand ? $this->item->brand->toArray() : null;

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

    public function getPrefixedIdAttribute()
    {
        if ($this->variant) {
            return 'variant-' . $this->variant->id;
        }

        return 'product-' . $this->product->id;
    }

    public function getItemDataAttribute()
    {
        $model = $this->variant ?? $this->product;

        $data          = $model->attributesToArray();
        $data['price'] = $model->price;
        unset($data['description']);

        return $data;
    }

    public function getCustomFieldValueDescriptionAttribute()
    {
        return $this->custom_field_values->map(function (CustomFieldValue $value) {
            return sprintf('%s: %s', e($value->custom_field->name), $value->display_value);
        })->implode('<br />');
    }
}
