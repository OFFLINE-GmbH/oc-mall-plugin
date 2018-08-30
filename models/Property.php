<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\QueryBuilder;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Queries\UniquePropertyValuesInCategories;
use OFFLINE\Mall\Classes\Queries\UniquePropertyValuesInCategoriesQuery;
use OFFLINE\Mall\Classes\Traits\HashIds;

class Property extends Model
{
    use Validation;
    use SoftDelete;
    use HashIds;
    use Sluggable;

    protected $dates = ['deleted_at'];
    public $jsonable = ['options'];
    public $rules = [
        'name' => 'required',
        'type' => 'required|in:text,textarea,dropdown,checkbox,color,image',
    ];
    public $slugs = [
        'slug' => 'name',
    ];
    public $table = 'offline_mall_properties';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'unit',
    ];
    public $fillable = [
        'name',
        'type',
        'unit',
        'slug',
        'options',
    ];
    public $hasMany = [
        'property_values' => PropertyValue::class,
    ];
    public $belongsToMany = [
        'property_groups' => [
            PropertyGroup::class,
            'table'      => 'offline_mall_property_group_property',
            'key'        => 'property_id',
            'otherKey'   => 'property_group_id',
            'pivot'      => ['use_for_variants', 'filter_type', 'sort_order'],
            'pivotModel' => PropertyGroupProperty::class,
        ],
    ];

    public function getSortOrderAttribute()
    {
        return $this->pivot->sort_order;
    }

    public static function getValuesForCategory(Collection $properties, $categories)
    {
        $raw = (new UniquePropertyValuesInCategoriesQuery($categories))->query()->get();

        $values = PropertyValue::hydrate($raw->toArray())->load('property');
        $values = $values->groupBy('property_id')->map(function ($values) {
            // if this property has options make sure to restore the original order
            $firstProp = $values->first()->property;
            if ( ! $firstProp->options) {
                return $values;
            }

            $order = collect($firstProp->options)->flatten()->flip();

            return $values->sortBy(function ($value) use ($order) {
                return $order[$value->value] ?? 0;
            });
        });

        return $values;
    }

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
