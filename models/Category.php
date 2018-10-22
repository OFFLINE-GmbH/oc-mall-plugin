<?php namespace OFFLINE\Mall\Models;

use Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Model;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\PropertyRemovalUpdate;
use OFFLINE\Mall\Classes\Traits\Category\MenuItems;
use OFFLINE\Mall\Classes\Traits\Category\Slug;
use OFFLINE\Mall\Classes\Traits\Category\Translation;
use OFFLINE\Mall\Classes\Traits\Category\Properties;
use OFFLINE\Mall\Classes\Traits\SortableRelation;
use System\Models\File;

class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use SortableRelation;
    use MenuItems;
    use Slug;
    use Translation;
    use Properties;

    /**
     * Cache key to store the slug/id map.
     * @var string
     */
    public const MAP_CACHE_KEY = 'oc-mall.categories.map';
    /**
     * Cache key to store the category tree.
     * @var string
     */
    public const TREE_CACHE_KEY = 'oc-mall.categories.tree';
    /**
     * This locale is used if RainLab.Translate is not available.
     * @var string
     */
    public const DEFAULT_LOCALE = 'default';

    protected $dates = [
        'deleted_at',
    ];
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'meta_description',
        'meta_title',
    ];
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];
    public $rules = [
        'name' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
    ];
    public $fillable = [
        'name',
        'slug',
        'code',
        'meta_title',
        'meta_description',
        'parent_id',
        'inherit_property_groups',
        'sort_order',
    ];
    public $casts = [
        'inherit_property_groups' => 'boolean',
    ];
    public $table = 'offline_mall_categories';
    public $hasMany = [
        'products'          => [
            Product::class,
        ],
        'publishedProducts' => [
            Product::class,
            'scope' => 'published',
        ],
    ];
    public $belongsToMany = [
        'property_groups' => [
            PropertyGroup::class,
            'table'    => 'offline_mall_category_property_group',
            'key'      => 'category_id',
            'otherKey' => 'property_group_id',
            'pivot'    => ['relation_sort_order'],
        ],
    ];
    public $attachOne = [
        'image' => File::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Update the index for all products that are affected by this property group removal.
        $this->bindEvent('model.relation.afterDetach', function ($relation, $groups) {
            if ($relation !== 'property_groups' || count($groups) < 1) {
                return;
            }

            $properties = $this->getPropertiesInGroups($groups)->toArray();

            // Fetch all child categories that inherit this categories properties.
            $categories = $this->scopeAllChildren(self::newQuery())
                               ->where('inherit_property_groups', true)
                               ->get()
                               ->concat([$this]);

            // Chunk the deletion and re-indexing since a lot of products and variants
            // might be affected by this change.
            Product::published()
                   ->orderBy('id')
                   ->whereIn('category_id', $categories->pluck('id'))
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

    /**
     * Boot method of the Category model
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function (self $category) {
            if ($category->parent_id === null) {
                $category->inherit_property_groups = false;
            }
            if ($category->inherit_property_groups === true && $category->property_groups()->count() > 0) {
                $category->property_groups()->detach();
            }
            if ( ! $category->slug) {
                $category->slug = str_slug($category->name);
            }
        });
        static::saving(function (self $model) {
            $model->validateUniqueSlug($model);
        });
        static::saved(function (self $model) {
            $model->purgeCache();
            $model->warmCache();
        });
        static::deleted(function (self $model) {
            $model->purgeCache();
            $model->warmCache();
        });
    }

    /**
     * Don't show the inherits_property_groups field if this
     * category i a root node.
     */
    public function filterFields($fields, $context = null)
    {
        if (isset($fields->inherit_property_groups)) {
            $fields->inherit_property_groups->hidden = $this->parent_id === null;
        }
    }

    /**
     * Returns an array with possible parent categories.
     *
     * If we are in create mode (no id) all categories are returned.
     * If an id is set, we need to exclude the current node itself to
     * prevent an infinite parent-child relationship.
     *
     * @return array
     */
    public function getParentOptions()
    {
        $options = [
            // null key for "no parent"
            null => '(' . trans('offline.mall::lang.category.no_parent') . ')',
        ];

        // In edit mode, exclude the node itself.
        $items = $this->id ? Category::withoutSelf()->get() : Category::getAll();
        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = sprintf('%s %s', str_repeat('--', $item->getLevel()), $item->name);
        });

        return $options;
    }

    /**
     * Returns an array of all available sorting options.
     *
     * @return array
     */
    public static function allowedSortingOptions()
    {
        $name    = trans('offline.mall::lang.product.name');
        $created = trans('offline.mall::lang.common.created_at');

        return [
            'name asc'        => "${name}, A->Z",
            'name desc'       => "${name}, Z->A",
            'created_at asc'  => "${created}, A->Z",
            'created_at desc' => "${created}, Z->A",
        ];
    }

    /**
     * Return an array of all child category ids.
     *
     * @return array
     */
    public function getChildrenIds()
    {
        return $this->scopeAllChildren(self::newQuery(), true)
                    ->get(['id'])
                    ->pluck('id')
                    ->toArray();
    }
}
