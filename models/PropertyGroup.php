<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\SortableRelation;

class PropertyGroup extends Model
{
    use Validation;
    use Sluggable;
    use SortableRelation;

    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name' => 'required',
    ];
    public $fillable = ['name', 'display_name', 'slug', 'description'];

    public $table = 'offline_mall_property_groups';

    public $belongsToMany = [
        'properties'            => [
            Property::class,
            'table'      => 'offline_mall_property_property_group',
            'key'        => 'property_group_id',
            'otherKey'   => 'property_id',
            'pivot'      => ['use_for_variants', 'filter_type', 'sort_order'],
            'pivotModel' => PropertyGroupProperty::class,
        ],
        'filterable_properties' => [
            Property::class,
            'table'      => 'offline_mall_property_property_group',
            'key'        => 'property_group_id',
            'otherKey'   => 'property_id',
            'pivot'      => ['use_for_variants', 'filter_type', 'sort_order'],
            'pivotModel' => PropertyGroupProperty::class,
            'order'      => 'offline_mall_property_property_group.sort_order ASC',
            'conditions' => 'offline_mall_property_property_group.filter_type is not null',
        ],
        'categories'            => [
            Category::class,
            'table'    => 'offline_mall_category_property_group',
            'key'      => 'property_group_id',
            'otherKey' => 'category_id',
            'pivot'    => ['sort_order'],
        ],
    ];

    public function getDisplayNameAttribute()
    {
        if ($this->getOriginal('display_name')) {
            return $this->getOriginal('display_name');
        }

        return $this->name;
    }
}
