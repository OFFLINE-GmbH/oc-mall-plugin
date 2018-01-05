<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class Property extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public $jsonable = ['options'];

    public $rules = [
        'name' => 'required',
        'type' => 'required|in:text,textarea,dropdown,checkbox,color,image',
    ];

    public $table = 'offline_mall_properties';

    public function getTypeOptions()
    {
        return [
            'text'     => trans('offline.mall::lang.custom_field_options.text'),
            'textarea' => trans('offline.mall::lang.custom_field_options.textarea'),
            'dropdown' => trans('offline.mall::lang.custom_field_options.dropdown'),
            'checkbox' => trans('offline.mall::lang.custom_field_options.checkbox'),
            'color'    => trans('offline.mall::lang.custom_field_options.color'),
//            'image'    => trans('offline.mall::lang.custom_field_options.image'),
        ];
    }
}
