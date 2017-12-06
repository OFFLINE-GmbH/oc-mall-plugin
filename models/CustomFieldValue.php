<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class CustomFieldValue extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'cart_product_id' => 'exists:offline_mall_cart_products,id',
        'custom_field_id' => 'exists:offline_mall_product_custom_fields,id',
    ];

    public $table = 'offline_mall_cart_custom_field_value';

    public $belongsTo = [
        'cart_product'        => CartProduct::class,
        'custom_field'        => CustomField::class,
        'custom_field_option' => CustomFieldOption::class,
    ];
}
