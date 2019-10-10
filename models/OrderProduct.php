<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

class OrderProduct extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    use JsonPrice {
        useCurrency as fallbackCurrency;
    }

    protected $dates = ['deleted_at'];

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

    public $casts = [
        'weight'       => 'integer',
        'width'        => 'integer',
        'length'       => 'integer',
        'height'       => 'integer',
        'total_weight' => 'integer',
        'stackable'    => 'boolean',
        'shippable'    => 'boolean',
        'taxable'      => 'boolean',
        'is_virtual'   => 'boolean',
    ];

    public $jsonable = [
        'taxes',
        'item',
        'custom_field_values',
        'property_values',
        'brand',
        'service_options',
    ];

    public $table = 'offline_mall_order_products';

    public $belongsTo = [
        'variant' => Variant::class,
        'product' => Product::class,
        'order'   => Order::class,
    ];
    public $hasMany = [
        'product_file_grants' => ProductFileGrant::class,
    ];

    public function scopeVirtual($query)
    {
        return $query->where('is_virtual', true);
    }

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

    protected function useCurrency()
    {
        if ($this->currency) {
            return new Currency($this->currency);
        }

        return $this->fallbackCurrency();
    }

    public function pricePreTaxes()
    {
        return $this->toPriceModel('price_pre_taxes');
    }

    public function priceTaxes()
    {
        return $this->toPriceModel('price_taxes');
    }

    public function pricePostTaxes()
    {
        return $this->toPriceModel('price_post_taxes');
    }

    public function totalPreTaxes()
    {
        return $this->toPriceModel('total_pre_taxes');
    }

    public function totalTaxes()
    {
        return $this->toPriceModel('total_taxes');
    }

    public function totalPostTaxes()
    {
        return $this->toPriceModel('total_post_taxes');
    }

    /**
     * Return the id with a 'product/variant' prefix.
     */
    public function getPrefixedIdAttribute()
    {
        $kind      = $this->variant_id ? 'variant' : 'product';
        $attribute = $this->variant_id ? 'variant_id' : 'product_id';

        return $kind . '-' . $this->{$attribute};
    }

    protected function toPriceModel(string $key): Price
    {
        return new Price([
            'currency_id' => $this->useCurrency()->id,
            'price'       => $this->getOriginal($key) / 100,
        ]);
    }

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
}
