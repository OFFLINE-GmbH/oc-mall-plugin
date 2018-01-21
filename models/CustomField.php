<?php namespace OFFLINE\Mall\Models;

use DB;
use Model;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

class CustomField extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;
    use HashIds;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = ['name'];
    public $with = ['custom_field_options'];
    public $casts = [
        'required' => 'boolean'
    ];

    public $rules = [
        'product_id' => 'exists:offline_mall_products,id',
        'name'       => 'required',
        'type'       => 'in:text,textarea,dropdown,checkbox,color,image',
        'required'   => 'boolean',
    ];
    public $hasMany = [
        'custom_field_options' => [CustomFieldOption::class, 'order' => 'sort_order'],
    ];
    public $belongsToMany = [
        'variants' => [
            Variant::class,
            'table'    => 'offline_mall_product_variant_custom_field_option',
            'key'      => 'custom_field_option_id',
            'otherKey' => 'variant_id',
        ],
        'products' => [
            Product::class,
            'table'    => 'offline_mall_product_custom_field',
            'key'      => 'custom_field_id',
            'otherKey' => 'product_id',
        ],
    ];

    public $attachOne = [
        'image' => File::class,
    ];

    public $table = 'offline_mall_custom_fields';

    public function getTypeLabelAttribute()
    {
        return trans('offline.mall::lang.custom_field_options.' . $this->type);
    }

    public function getTypeDropdownAttribute()
    {
        return $this->type;
    }
}
