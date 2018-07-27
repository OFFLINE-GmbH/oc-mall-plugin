<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
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

    public function getValuesAttribute()
    {
        return $this->property_values->reject(function (PropertyValue $value) {
            return $value->value === '' || $value->value === null;
        })->unique('value');
    }

    public function getValuesForCategory($category)
    {
        $products      = $category->publishedProducts;
        $productValues = PropertyValue::where('describable_type', Product::MORPH_KEY)
                                      ->whereIn('describable_id', $products->pluck('id'))
                                      ->where('property_id', $this->id)
                                      ->groupBy('value')
                                      ->get();

        $variantValues = PropertyValue::where('describable_type', Variant::MORPH_KEY)
                                      ->whereIn('describable_id', $products->flatMap->variants->pluck('id'))
                                      ->where('property_id', $this->id)
                                      ->groupBy('value')
                                      ->get();

        $values = $productValues->merge($variantValues)->unique('value');

        // If this property has options make sure to restore the original order
        if ($this->options) {
            $order  = collect($this->options)->pluck('value')->flip();
            $values = $values->sortBy(function ($value) use ($order) {
                return $order[$value->value] ?? 0;
            });
        }

        return $values;
    }

    public function getMinValueAttribute()
    {
        return $this->values->min('value');
    }

    public function getMaxValueAttribute()
    {
        return $this->values->max('value');
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
