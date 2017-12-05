<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Tax extends Model
{
    use Validation;

    public $rules = [
        'name'       => 'required',
        'percentage' => 'numeric|min:0|max:100',
    ];

    public $table = 'offline_mall_taxes';

    public $belongsToMany = [
        'products'         => [
            Product::class,
            'table'    => 'offline_mall_product_tax',
            'key'      => 'tax_id',
            'otherKey' => 'product_id',
        ],
        'shipping_methods' => [
            ShippingMethod::class,
            'table'    => 'offline_mall_shipping_method_tax',
            'key'      => 'tax_id',
            'otherKey' => 'shipping_method_id',
        ],
        'countries' => [
            Country::class,
            'table'    => 'offline_mall_country_tax',
            'key'      => 'tax_id',
            'otherKey' => 'country_id',
        ],
    ];

    public function getPercentageDecimalAttribute()
    {
        return (float)$this->percentage / 100;
    }
}
