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
        'name'         => 'required',
        'price'        => 'required|integer',
        'total_price'  => 'required|integer',
        'quantity'     => 'required|integer',
        'weight'       => 'integer',
        'width'        => 'integer',
        'length'       => 'integer',
        'height'       => 'integer',
        'total_weight' => 'integer',
        'stackable'    => 'boolean',
        'shippable'    => 'boolean',
        'taxable'      => 'boolean',
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
        'custom_fields',
    ];

    public $table = 'offline_mall_order_products';

    public function getPriceColumns()
    {
        return [
            'price',
            'total_price',
        ];
    }
}
