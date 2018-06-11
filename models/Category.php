<?php namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Controller;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Model;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\SortableRelation;
use System\Models\File;

class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use Sluggable;
    use SortableRelation;

    public const ID_MAP_CACHE_KEY = 'oc-mall.categories.id_map';

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

    public $slugs = [
        'slug' => 'name',
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
            'pivot'    => ['sort_order'],
        ],
    ];

    public $attachOne = [
        'image' => File::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $category) {
            if ($category->parent_id === null) {
                $category->inherit_property_groups = false;
            }
            if ($category->inherit_property_groups === true) {
                $category->property_groups()->detach();
            }
        });
        static::saved(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
        });
        static::deleted(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
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

    public static function getMenuTypeInfo($type)
    {
        $result = [];
        if ($type == 'mall-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
            ];
        }

        if ($type === 'all-mall-categories') {
            $result = [
                'dynamicItems' => true,
            ];
        }

        return $result;
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::getNested();
        $iterator = function($categories) use (&$iterator) {
            $result = [];
            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children)
                    ];
                }
            }
            return $result;
        };
        return $iterator($category);
    }

    /**
     * @param $item
     * @param $url
     * @param $theme
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $structure = [];

        if ( ! $pageUrl = GeneralSettings::get('category_page')) {
            throw new InvalidArgumentException(
                'Mall: Please select a category page via the backend settings.'
            );
        }

        if($item->type == 'mall-category') {
            $category = self::find($item->reference);
            if (!$category) {
                return;
            }

            $controller = new Controller();
            $entryUrl = $controller->pageUrl($pageUrl, ['slug' => $category->slug]);

            $structure['url'] = $entryUrl;
            $structure['isActive'] = $entryUrl === $url;
            $structure['mtime'] = $category->updated_at;
            $structure['title'] = $category->name;
            $structure['code'] = $category->code;

        } elseif ($item->type == 'all-mall-categories') {
            $category  = new Category();

            $iterator = function ($items, $baseUrl = '') use (&$iterator, &$structure, $pageUrl, $url) {
                $branch = [];

                $controller = new Controller();
                foreach ($items as $item) {
                    $entryUrl               = $controller->pageUrl($pageUrl, ['slug' => $item->nestedSlug]);
                    $branchItem             = [];
                    $branchItem['url']      = $entryUrl;
                    $branchItem['isActive'] = $entryUrl === $url;
                    $branchItem['title']    = $item->name;
                    $branchItem['code']     = $item->code;

                    if ($item->children) {
                        $branchItem['items'] = $iterator($item->children, $item->slug);
                    }

                    $branch[] = $branchItem;
                }

                return $branch;
            };

            $structure['items'] = $iterator($category->getEagerRoot());
        }

        return $structure;
    }

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

    public static function getByNestedSlug(string $slug, array $with = [])
    {
        $slug = trim($slug, ' /');

        $slugMap = (new Category)->getSlugMap();
        if ( ! isset($slugMap[$slug])) {
            return null;
        }

        return Category::with($with)->findOrFail($slugMap[$slug]);
    }

    /**
     * Returns a cached map of category_ids and slug pairs.
     */
    public function getSlugMap()
    {
        return \Cache::remember(self::ID_MAP_CACHE_KEY, 60 * 24, function () {
            $map = [];

            $buildSlugMap = function (?Category $parent = null, array &$map, string $base = '') use (&$buildSlugMap) {
                $slug       = trim($base . '/' . $parent->slug, '/');
                $map[$slug] = $parent->id;
                foreach ($parent->children as $child) {
                    $buildSlugMap($child, $map, $slug);
                }
            };

            foreach (Category::getAllRoot() as $parent) {
                $buildSlugMap($parent, $map);
            }

            return $map;
        });
    }

    /**
     * Returns the slug of this category prepended by
     * the slugs of each parent category.
     *
     * @return string
     */
    public function getNestedSlugAttribute()
    {
        return $this->getParentsAndSelf()->map(function (Category $category) {
            return $category->slug;
        })->implode('/');
    }

    public function getInheritedPropertyGroupsAttribute()
    {
        return $this->inherit_property_groups ? $this->getInheritedPropertyGroups() : $this->property_groups;
    }

    /**
     * Returns the property groups of the first parent
     * that does not inherit them.
     */
    public function getInheritedPropertyGroups()
    {
        $groups = $this->getParents()->first(function (Category $category) {
            return ! $category->inherit_property_groups;
        })->property_groups;

        if ($groups) {
            $groups->load([
                'properties' => function ($q) {
                    $q->wherePivot('filter_type', '<>', null);
                },
            ]);
        }

        return $groups ?? new Collection();
    }

    /**
     * Returns all products in this category.
     *
     * @param bool $useVariants
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProducts($useVariants = true)
    {
        $this->publishedProducts->load(['variants', 'variants.image_sets']);

        $items = $this->publishedProducts->flatMap(function (Product $product) {
            return $product->inventory_management_method === 'variant' ? $product->variants : [$product];
        });

        if ($useVariants) {
            return $items;
        }

        // If the variants should not be listed separately we select
        // all parent products with properties matching the current filter
        // criteria.
        $productIds = $items->map(function ($item) {
            return $item->product_id ?? $item->id;
        })->unique();

        return Product::with('variants')->whereIn('id', $productIds)->get();
    }

    /**
     * Returnes a flattened Collection of all available properties.
     *
     * @return Collection
     */
    public function getPropertiesAttribute()
    {
        return $this->load('property_groups.properties')->property_groups->map->properties->flatten();
    }
}
