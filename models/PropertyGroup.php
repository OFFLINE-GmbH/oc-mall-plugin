<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Queue;
use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\PropertyRemovalUpdate;
use OFFLINE\Mall\Classes\Traits\SortableRelation;

class PropertyGroup extends Model
{
    use Validation;
    use Sluggable;
    use SortableRelation;
    use Sortable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'display_name',
        'description',
    ];

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
            'pivot'    => ['relation_sort_order'],
        ],
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Update the index for all products that are affected when properties are removed from this group.
        $this->bindEvent('model.relation.afterDetach', function ($relation, $properties) {
            if ($relation !== 'properties' || count($properties) < 1) {
                return;
            }

            // Fetch all categories that use this property group. The property values for each
            // product and variant in these categories has to be deleted. Furthermore, the
            // products have to be re-indexed after the modification is done.
            $categories = $this->getRelatedCategories();

            // Chunk the deletion and re-indexing since a lot of products and variants
            // might be affected by this change.
            Product::published()
                   ->orderBy('id')
                   ->whereHas('categories', function ($q) use ($categories) {
                       $q->whereIn('category_id', $categories->pluck('id'));
                   })
                   ->with('variants')
                   ->chunk(25, function ($products) use ($properties) {
                       $data = [
                           'properties' => $properties,
                           'products'   => $products->pluck('id'),
                           'variants'   => $products->flatMap->variants->pluck('id'),
                       ];
                       Queue::push(PropertyRemovalUpdate::class, $data);
                   });
        });
    }

    public function getDisplayNameAttribute()
    {
        if ($this->getOriginal('display_name')) {
            return $this->getAttributeTranslated('display_name');
        }

        return $this->name;
    }

    public function getRelatedCategories()
    {
        return $this->categories->flatMap(function (Category $category) {
            return $category->getAllChildrenAndSelf()->filter(function (Category $category) {
                return $category->inherit_property_groups === true || $category->nest_depth === 0;
            });
        });
    }
}
