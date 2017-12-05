<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class Variant extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public $rules = [
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer',
        'allow_out_of_stock_purchases' => 'boolean',
    ];

    public $table = 'offline_mall_product_variants';

    public $belongsTo = [
        'product' => Product::class,
    ];

    public $belongsToMany = [
        'custom_field_options' => [
            CustomFieldOption::class,
            'table'    => 'offline_mall_product_variant_custom_field_option',
            'key'      => 'variant_id',
            'otherKey' => 'custom_field_option_id',
        ],
    ];
}
