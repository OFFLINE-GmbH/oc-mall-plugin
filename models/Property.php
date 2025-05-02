<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Queue;
use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\SortableRelation;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\PropertyRemovalUpdate;
use OFFLINE\Mall\Classes\Traits\HashIds;

class Property extends Model
{
    use Validation;
    use SoftDelete;
    use HashIds;
    use Sluggable;
    use SortableRelation;

    public $jsonable = ['options'];

    public $rules = [
        'name' => 'required',
        'type' => 'required|in:text,textarea,dropdown,checkbox,color,image,float,integer,richeditor,switch,datetime,date',
    ];

    public $slugs = [
        'slug' => 'name',
    ];

    public $table = 'offline_mall_properties';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'name',
        'unit',
        'options',
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
            'table'      => 'offline_mall_property_property_group',
            'key'        => 'property_id',
            'otherKey'   => 'property_group_id',
            'pivot'      => ['use_for_variants', 'filter_type', 'sort_order'],
            'pivotModel' => PropertyGroupProperty::class,
        ],
    ];

    protected $dates = ['deleted_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->bindEvent('model.relation.attach', function ($relationName, $attachedIdList, $insertData) {
            if ($relationName === 'property_groups') {
                foreach ($attachedIdList as $attachedId) {
                    $propertyGroup = PropertyGroup::find($attachedId);
                    UniquePropertyValue::updateUsingPropertyGroup($propertyGroup);
                }
            }
        });

        $this->bindEvent('model.relation.detach', function ($relationName, $detachedIdList) {
            if ($relationName === 'property_groups') {
                foreach ($detachedIdList as $detachedId) {
                    $propertyGroup = PropertyGroup::find($detachedId);
                    UniquePropertyValue::updateUsingPropertyGroup($propertyGroup);
                }
            }
        });
    }

    public function afterSave()
    {
        if ($this->pivot && ! $this->pivot->use_for_variants) {
            $categories = $this->property_groups->flatMap->getRelatedCategories();

            Product::whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('offline_mall_category_product.category_id', $categories->pluck('id'));
            })
                ->where('group_by_property_id', $this->id)
                ->update(['group_by_property_id' => null]);
        }

        UniquePropertyValue::updateUsingProperty($this);
    }

    public function afterDelete()
    {
        // Remove the property values from all related products.
        $products = $this->property_values->pluck('product_id')->unique();

        // Chunk the re-indexing since a lot of products and variants might be affected by this change.
        Product::published()
            ->orderBy('id')
            ->whereIn('id', $products)
            ->with('variants')
            ->chunk(25, function ($products) {
                $data = [
                    'properties' => [$this->id],
                    'products'   => $products->pluck('id'),
                    'variants'   => $products->flatMap->variants->pluck('id'),
                ];
                Queue::push(PropertyRemovalUpdate::class, $data);
            });
    }

    public function getSortOrderAttribute()
    {
        return $this->pivot->sort_order;
    }

    public static function getValuesForCategory($categories)
    {
        $values = UniquePropertyValue::hydratePropertyValuesForCategories($categories)
            ->load(['property.translations', 'translations']);

        return $values->groupBy('property_id')->map(function ($values) {
            // if this property has options make sure to restore the original order
            $firstProp = $values->first()->property;

            if (! $firstProp->options) {
                return $values;
            }

            $order = collect($firstProp->options)->flatten()->filter()->flip();

            return $values->sortBy(fn ($value) => $order[$value->value] ?? 0);
        });
    }

    public function getTypeOptions()
    {
        return [
            'text'       => trans('offline.mall::lang.custom_field_options.text'),
            'integer'    => trans('offline.mall::lang.custom_field_options.integer'),
            'float'      => trans('offline.mall::lang.custom_field_options.float'),
            'textarea'   => trans('offline.mall::lang.custom_field_options.textarea'),
            'richeditor' => trans('offline.mall::lang.custom_field_options.richeditor'),
            'dropdown'   => trans('offline.mall::lang.custom_field_options.dropdown'),
            'checkbox'   => trans('offline.mall::lang.custom_field_options.checkbox'),
            'color'      => trans('offline.mall::lang.custom_field_options.color'),
            //            'image'    => trans('offline.mall::lang.custom_field_options.image'),
            'datetime'   => trans('offline.mall::lang.custom_field_options.datetime'),
            'date'       => trans('offline.mall::lang.custom_field_options.date'),
            'switch'     => trans('offline.mall::lang.custom_field_options.switch'),
        ];
    }
}
