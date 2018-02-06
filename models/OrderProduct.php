<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class OrderProduct extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use Price;

    protected $dates = ['deleted_at'];

    public $rules = [
        'name'             => 'required',
        'order_id'         => 'required',
        'product_id'       => 'required',
        'price_pre_taxes'  => 'required',
        'price_taxes'      => 'required',
        'price_post_taxes' => 'required',
        'total_pre_taxes'  => 'required',
        'total_taxes'      => 'required',
        'total_post_taxes' => 'required',
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
        'property_values'
    ];

    public $table = 'offline_mall_order_products';

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
}
