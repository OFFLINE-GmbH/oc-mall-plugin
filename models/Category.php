<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Cache;
use DB;
use Illuminate\Support\Facades\Queue;
use Model;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\SortableRelation;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Collection;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Jobs\PropertyRemovalUpdate;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Classes\Traits\Category\MenuItems;
use OFFLINE\Mall\Classes\Traits\Category\Properties;
use OFFLINE\Mall\Classes\Traits\Category\Slug;
use OFFLINE\Mall\Classes\Traits\Category\Translation;
use System\Models\File;

class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use SortableRelation {
        setSortableRelationOrder as traitSetSortableRelationOrder;
    }
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

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_categories';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'meta_description',
        'meta_title',
        'description',
        'description_short',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'slug',
        'code',
        'description_short',
        'description',
        'meta_title',
        'meta_description',
        'parent_id',
        'inherit_property_groups',
        'inherit_review_categories',
        'sort_order',
        'google_product_category_id',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'inherit_property_groups'   => 'boolean',
        'inherit_review_categories' => 'boolean',
        'deleted_at'                => 'datetime',
    ];

    /**
     * The attachOne relationships of this model.
     * @var array
     */
    public $attachOne = [
        'image' => File::class,
    ];

    /**
     * The belongsToMany relationships of this model.
     * @var array
     */
    public $belongsToMany = [
        'products'          => [
            Product::class,
            'table'    => 'offline_mall_category_product',
            'key'      => 'category_id',
            'otherKey' => 'product_id',
            'pivot'    => ['sort_order'],
            'pivotSortable' => 'sort_order',
        ],
        'publishedProducts' => [
            Product::class,
            'table'    => 'offline_mall_category_product',
            'key'      => 'category_id',
            'otherKey' => 'product_id',
            'scope'    => 'published',
            'pivot'    => ['sort_order'],
        ],
        'property_groups'   => [
            PropertyGroup::class,
            'table'    => 'offline_mall_category_property_group',
            'key'      => 'category_id',
            'otherKey' => 'property_group_id',
            'pivot'    => ['relation_sort_order'],
        ],
        'review_categories' => [
            ReviewCategory::class,
            'table' => 'offline_mall_category_review_category',
        ],
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

        $this->bindEvent('model.relation.attach', function ($relationName, $attachedIdList, $insertData) {
            if ($relationName === 'property_groups') {
                UniquePropertyValue::updateUsingCategory($this);
            }
        });

        $this->bindEvent('model.relation.detach', function ($relationName, $detachedIdList) {
            if ($relationName === 'property_groups') {
                UniquePropertyValue::updateUsingCategory($this);
            }
        });
    }

    /**
     * Boot method of the Category model
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function (self $model) {
            if ($model->parent_id === null) {
                $model->inherit_property_groups = false;
            }

            if ($model->parent_id === null) {
                $model->inherit_review_categories = false;
            }

            if ($model->inherit_property_groups === true && $model->property_groups()->count() > 0) {
                $model->property_groups()->detach();
            }

            if ($model->inherit_review_categories === true && $model->review_categories()->count() > 0) {
                $model->review_categories()->detach();
            }

            if (! $model->slug) {
                $model->slug = str_slug($model->name);
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
            DB::table('offline_mall_category_product')->where('category_id', $model->id)->delete();
            $model->purgeCache();
            $model->warmCache();
        });
    }

    /**
     * Don't show the inherit_* fields if this category i a root node.
     * @param mixed $fields
     * @param null|mixed $context
     */
    public function filterFields($fields, $context = null)
    {
        if (isset($fields->inherit_property_groups)) {
            $fields->inherit_property_groups->hidden = $this->parent_id === null;
        }

        if (isset($fields->inherit_review_categories)) {
            $fields->inherit_review_categories->hidden = $this->parent_id === null;
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
        $items = $this->id ? Category::withoutSelf()->get() : Category::getAll();

        return [
            // null key for "no parent"
            null => '(' . trans('offline.mall::lang.category.no_parent') . ')',
        ] + $items->listsNested('name', 'id');
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
            'name asc'        => "{$name}, A->Z",
            'name desc'       => "{$name}, Z->A",
            'created_at asc'  => "{$created}, A->Z",
            'created_at desc' => "{$created}, Z->A",
        ];
    }

    public function getInheritedReviewCategoriesAttribute()
    {
        return $this->inherit_review_categories ? $this->getInheritedReviewCategories() : $this->review_categories;
    }

    public function afterSave(): void
    {
        UniquePropertyValue::updateUsingCategory($this);
    }

    /**
     * Returns the review categories of the first parent
     * that does not inherit them.
     */
    public function getInheritedReviewCategories()
    {
        $groups = $this->getParents()->first(fn (Category $category) => ! $category->inherit_review_categories)->review_categories;

        return $groups ?? new Collection();
    }

    public function setSortableRelationOrder($relationName, $itemIds, $referencePool = null)
    {
        $this->traitSetSortableRelationOrder($relationName, $itemIds, $referencePool);

        $this->products->each(function (Product $product) {
            Cache::forget(Product::sortOrderCacheKey($product->id));
            (new ProductObserver(app(Index::class)))->updated($product);
        });
    }
}
