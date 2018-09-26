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
    ];

    public $jsonable = [
        'taxes',
        'item',
        'custom_field_values',
        'property_values',
    ];

    public $table = 'offline_mall_order_products';

    public $belongsTo = [
        'variant' => Variant::class,
        'order' => Order::class,
    ];

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
}
