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

    public $table = 'offline_mall_property_groups';

    public $belongsToMany = [
        'properties' => [
            Property::class,
            'table'      => 'offline_mall_property_property_group',
            'key'        => 'property_group_id',
            'otherKey'   => 'property_id',
            'pivot'      => ['use_for_variants', 'filter_type', 'sort_order'],
            'pivotModel' => PropertyGroupProperty::class,
            'order'      => 'pivot_sort_order ASC',
        ],
        'categories' => [
            Category::class,
            'table'    => 'offline_mall_category_property_group',
            'key'      => 'property_id',
            'otherKey' => 'category_id',
            'pivot'    => ['sort_order'],
        ],
    ];
}
