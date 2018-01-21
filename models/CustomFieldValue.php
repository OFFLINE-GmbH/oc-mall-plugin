<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class CustomFieldValue extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;

    public $rules = [
        'cart_product_id' => 'exists:offline_mall_cart_products,id',
        'custom_field_id' => 'exists:offline_mall_custom_fields,id',
    ];

    public $table = 'offline_mall_cart_custom_field_value';
    public $with = ['custom_field_option'];
    public $appends = ['price'];

    public $belongsTo = [
        'cart_product'        => CartProduct::class,
        'custom_field'        => CustomField::class,
        'custom_field_option' => CustomFieldOption::class,
    ];

    public function getPriceAttribute()
    {
        $option = optional($this->custom_field->custom_field_options)->find($this->custom_field_option_id);

        return optional($option)->price ? $option->price : $this->custom_field->price;
    }
}
