<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

class OrderProduct extends Model
{
    use JsonPrice {
        useCurrency as fallbackCurrency;
    }
    use SoftDelete;
    use Validation;

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_order_products';

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'             => 'required',
        'order_id'         => 'required',
        'product_id'       => 'required',
        'price_pre_taxes'  => 'nullable|required',
        'price_taxes'      => 'nullable|required',
        'price_post_taxes' => 'nullable|required',
        'total_pre_taxes'  => 'nullable|required',
        'total_taxes'      => 'nullable|required',
        'total_post_taxes' => 'nullable|required',
        'quantity'         => 'required',
        'weight'           => 'nullable|integer',
        'width'            => 'nullable|integer',
        'length'           => 'nullable|integer',
        'height'           => 'nullable|integer',
        'total_weight'     => 'nullable|integer',
        'stackable'        => 'boolean',
        'shippable'        => 'boolean',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'weight'        => 'integer',
        'width'         => 'integer',
        'length'        => 'integer',
        'height'        => 'integer',
        'total_weight'  => 'integer',
        'stackable'     => 'boolean',
        'shippable'     => 'boolean',
        'taxable'       => 'boolean',
        'is_virtual'    => 'boolean',
        'deleted_at'    => 'datetime',
    ];

    /**
     * Attribute names that are json encoded and decoded from the database.
     * @var array
     */
    public $jsonable = [
        'taxes',
        'item',
        'custom_field_values',
        'property_values',
        'brand',
        'service_options',
    ];

    /**
     * The belongsTo relationships of this model.
     * @var array
     */
    public $belongsTo = [
        'variant' => Variant::class,
        'product' => Product::class,
        'order'   => Order::class,
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'product_file_grants' => ProductFileGrant::class,
    ];

    /**
     * Add virtual query scope.
     * @var array
     * @param mixed $query
     */
    public function scopeVirtual($query)
    {
        return $query->where('is_virtual', true);
    }

    /**
     * Get price columns
     * @return void
     */
    public function getPriceColumns()
    {
        return [
            'price_pre_taxes',
            'price_taxes',
            'price_post_taxes',
            'total_pre_taxes',
            'total_taxes',
            'total_post_taxes',
        ];
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function pricePreTaxes()
    {
        return $this->toPriceModel('price_pre_taxes');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function priceTaxes()
    {
        return $this->toPriceModel('price_taxes');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function pricePostTaxes()
    {
        return $this->toPriceModel('price_post_taxes');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function totalPreTaxes()
    {
        return $this->toPriceModel('total_pre_taxes');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function totalTaxes()
    {
        return $this->toPriceModel('total_taxes');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function totalPostTaxes()
    {
        return $this->toPriceModel('total_post_taxes');
    }

    /**
     * Return the id with a 'product/variant' prefix.
     * @return mixed
     */
    public function getPrefixedIdAttribute()
    {
        $kind      = $this->variant_id ? 'variant' : 'product';
        $attribute = $this->variant_id ? 'variant_id' : 'product_id';

        return $kind . '-' . $this->{$attribute};
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function getCustomFieldValueDescriptionAttribute()
    {
        return collect($this->custom_field_values)->map(function (array $value) {
            $display = e($value['custom_field_option']['name'] ?? $value['display_value']);

            // Color values that were user picked don't have to be escaped. They contain html that
            // was already escaped during the creation of the order.
            if ($value['custom_field_option_id'] === null && $value['custom_field']['type'] === 'color') {
                $display = $value['display_value'];
            }

            return sprintf(
                '%s: %s',
                e($value['custom_field']['name']),
                $display
            );
        })->implode('<br />');
    }

    /**
     * Set used Currency
     * @return Currency
     */
    protected function useCurrency()
    {
        if ($this->currency) {
            return new Currency($this->currency);
        } elseif ($this->order->currency) {
            return new Currency($this->order->currency);
        } else {
            return $this->fallbackCurrency();
        }
    }

    /**
     * Undocumented function
     * @param string $key
     * @return mixed
     */
    protected function toPriceModel(string $key): Price
    {
        return new Price([
            'currency_id' => $this->useCurrency()->id,
            'price'       => $this->getOriginal($key) / 100,
        ]);
    }
}
