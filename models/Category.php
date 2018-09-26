<?php namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Controller;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Model;
use October\Rain\Database\QueryBuilder;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Queries\VariantsInCategoriesQuery;
use OFFLINE\Mall\Classes\Traits\SortableRelation;
use System\Models\File;

class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use SortableRelation;

    public const ID_MAP_CACHE_KEY = 'oc-mall.categories.id_map';
    public const ALL_CATEGORIES_CACHE_KEY = 'oc-mall.categories.all';

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
            if ( ! $category->slug) {
                $category->slug = str_slug($category->name);
            }
        });
        static::saved(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
            Cache::forget(self::ALL_CATEGORIES_CACHE_KEY);
        });
        static::deleted(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
            Cache::forget(self::ALL_CATEGORIES_CACHE_KEY);
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
                'references' => self::listSubCategoryOptions(),
            ];
        }

        if ($type === 'all-mall-categories') {
            $result = [
                'dynamicItems' => true,
            ];
        }

        return $result;
    }

    /**
     * Lists all categories with nested sub categories
     * This is used for the 'mall-category' menu type
     *
     * @return array
     */
    protected static function listSubCategoryOptions()
    {
        $category = self::getNested();
        $iterator = function ($categories) use (&$iterator) {
            $result = [];
            foreach ($categories as $category) {
                if ( ! $category->children) {
                    $result[$category->id] = $category->name;
                } else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children),
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
    public static function resolveCategoryItem($item, $url, $theme)
    {
        $category = self::find($item->reference);
        if ( ! $category) {
            return;
        }

        return self::getMenuItem($category, $url);
    }

    /**
     * @param $item
     * @param $url
     * @param $theme
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function resolveCategoriesItem($item, $url, $theme)
    {

        if (Cache::has(self::ALL_CATEGORIES_CACHE_KEY)) {
            return Cache::get(self::ALL_CATEGORIES_CACHE_KEY);
        }

        $structure = [];

        $category = new Category();

        $iterator = function ($items, $baseUrl = '') use (&$iterator, &$structure, $url) {
            $branch = [];

            foreach ($items as $item) {
                $branchItem = self::getMenuItem($item, $url);

                if ($item->children) {
                    $branchItem['items'] = $iterator($item->children, $item->slug);
                }

                $branch[] = $branchItem;
            }

            return $branch;
        };

        $structure['items'] = $iterator($category->getEagerRoot());

        Cache::forever(self::ALL_CATEGORIES_CACHE_KEY, $structure);

        return $structure;
    }

    /**
     * Creates a single menu item result array
     *
     * @param $item Category
     * @param $url  string
     *
     * @return array
     */
    protected static function getMenuItem($item, $url)
    {
        if ( ! $pageUrl = GeneralSettings::get('category_page')) {
            throw new InvalidArgumentException(
                'Mall: Please select a category page via the backend settings.'
            );
        }

        $controller = new Controller();
        $entryUrl   = $controller->pageUrl($pageUrl, ['slug' => $item->nestedSlug]);

        $result             = [];
        $result['url']      = $entryUrl;
        $result['isActive'] = $entryUrl === $url;
        $result['mtime']    = $item->updated_at;
        $result['title']    = $item->name;
        $result['code']     = $item->code;

        return $result;
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

    /**
     * This method is used in components to find the category either
     * by slug (if $id = ":slug") or by id.
     *
     * @param $slug
     * @param $id
     *
     * @return mixed
     */
    public static function bySlugOrId($slug, $id)
    {
        if ($id === ':slug') {
            return self::getByNestedSlug($slug);
        }

        return self::findOrFail($id);
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
        return \Cache::rememberForever(self::ID_MAP_CACHE_KEY, function () {
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
            $groups->load('properties');
        }

        return $groups ?? new Collection();
    }

    /**
     * Returns a flattened Collection of all available properties.
     *
     * @return Collection
     */
    public function getPropertiesAttribute()
    {
        return $this->load('property_groups.properties')->inherited_property_groups->map->properties->flatten();
    }

    /**
     * Return an array of all child category ids.
     *
     * @return array
     */
    public function getChildrenIds()
    {
        return $this->scopeAllChildren(\DB::table('offline_mall_categories'), true)
                    ->get(['id'])
                    ->pluck('id')
                    ->toArray();
    }
}
