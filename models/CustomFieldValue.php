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

    public $belongsTo = [
        'cart_product'        => CartProduct::class,
        'custom_field'        => CustomField::class,
        'custom_field_option' => CustomFieldOption::class,
    ];

    /**
     * Calculate the price depending on given data.
     *
     * If an option with a price is available use it. If the field itself
     * has a price, use it as fallback.
     * We allow to pass the fields and option parameter to save a few queries when
     * adding the Product to the cart since these relations are already available then.
     *
     * @param null|CustomField       $field
     * @param null|CustomFieldOption $option
     *
     * @return mixed|string
     */
    public function price(?CustomField $field = null, ?CustomFieldOption $option = null)
    {
        $field  = $field ?? $this->custom_field;
        $option = $option ?? optional($field->custom_field_options)->find($this->custom_field_option_id);

        return optional($option)->price ? $option->price : $field->price;
    }

    /**
     * Returns a raw html presentation of the attribute values.
     * The return value contains raw html and therefore is already escaped.
     * @return string
     */
    public function getDisplayValueAttribute()
    {
        $value = e($this->value);
        if ($this->custom_field->type === 'color') {
            return sprintf(
                '<span class="mall-color-swatch" style="display: inline-block; width: 10px; height: 10px; background: %s"></span>',
                $value
            );
        }
        if ($this->custom_field->type === 'checkbox') {
            return $this->value || $this->value === 'on' ? '&#10004;' : '&#10007;';
        }
        if ($this->custom_field->type === 'dropdown') {
            return $this->custom_field_option->name;
        }
        if ($this->custom_field->type === 'image') {
            return $this->custom_field_option->name;
        }

        return $value;
    }
}
