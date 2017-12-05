<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class ShippingMethod extends Model
{
    use Validation;
    use Sortable;
    use Price;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'name',
    ];

    public $rules = [
        'name'  => 'required',
        'price' => 'required|regex:/\d+([\.,]\d+)?/i',
    ];

    public $table = 'offline_mall_shipping_methods';

    public $hasMany = [
        'carts' => Cart::class,
        'rates' => ShippingMethodRate::class,
    ];

    public $belongsToMany = [
        'taxes' => [
            Tax::class,
            'table'    => 'offline_mall_shipping_method_tax',
            'key'      => 'shipping_method_id',
            'otherKey' => 'tax_id',
        ],
    ];
}
