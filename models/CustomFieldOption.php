<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class CustomFieldOption extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = ['name'];
    public $fillable = [
        'id',
        'name',
        'price',
        'sort_order',
        'custom_field_id',
    ];

    public $rules = [
        'name'  => 'required',
        'price' => 'regex:/\d+([\.,]\d+)?/i',
    ];

    public $belongsTo = [
        'product'      => Product::class,
        'custom_field' => CustomField::class,
    ];

    public $belongsToMany = [
        'variants' => [
            Variant::class,
            'table'    => 'offline_mall_product_variant_custom_field_option',
            'key'      => 'custom_field_option_id',
            'otherKey' => 'variant_id',
        ],
    ];

    public $table = 'offline_mall_product_custom_field_options';
}
