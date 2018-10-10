<?php namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Model;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\PropertyRemovalUpdate;
use OFFLINE\Mall\Classes\Traits\SortableRelation;
use System\Classes\PluginManager;
use System\Models\File;

class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use SortableRelation;

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
            'pivot'    => ['sort_order'],
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
     * Return all property ids that are in an array of group ids.
     */
    protected function getPropertiesInGroups(array $groupIds): Collection
    {
        return \DB::table('offline_mall_property_property_group')
                  ->where('property_group_id', $groupIds)
                  ->get(['property_id'])
                  ->pluck('property_id')
                  ->values();
    }

    /**
     * Translate url parameters when the user switches the active locale.
     *
     * @param $params
     * @param $oldLocale
     * @param $newLocale
     *
     * @return mixed
     */
    public static function translateParams($params, $oldLocale, $newLocale)
    {
        $newParams = $params;
        foreach ($params as $paramName => $paramValue) {
            $records = self::transWhere($paramName, $paramValue, $oldLocale)->first();
            if ($records) {
                $records->translateContext($newLocale);
                $newParams[$paramName] = $records->$paramName;
            } elseif ($paramName === 'slug') {
                // Translate nested slugs.
                $model    = new self;
                $category = array_get($model->getSlugMap($oldLocale), $newParams['slug'] ?? -1);
                if ( ! $category) {
                    continue;
                }

                $translationMap = array_flip($model->getSlugMap($newLocale));
                $translatedSlug = array_get($translationMap, $category);
                if ($translatedSlug) {
                    $newParams['slug'] = $translatedSlug;
                }
            }
        }

        return $newParams;
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
        if ($type === 'mall-category') {
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
        $category = new Category();
        $locale   = $category->getLocale();

        if (Cache::has($category->treeCacheKey($locale))) {
            return Cache::get($category->treeCacheKey($locale));
        }

        $structure = [];
        $iterator  = function ($items, $baseUrl = '') use (&$iterator, &$structure, $url, $locale) {
            $branch = [];
            foreach ($items as $item) {
                $branchItem = self::getMenuItem($item, $url);
                if ($locale !== static::DEFAULT_LOCALE && $item->rainlabTranslateInstalled()) {
                    $item->translateContext($locale);
                }
                if ($item->children) {
                    $branchItem['items'] = $iterator($item->children, $item->slug);
                }
                $branch[] = $branchItem;
            }

            return $branch;
        };

        $structure['items'] = $iterator($category->getEagerRoot());

        Cache::forever($category->treeCacheKey($locale), $structure);

        return $structure;
    }

    /**
     * Creates a single menu item result array
     *
     * @param $item Category
     * @param $url  string
     *
     * @return array
     * @throws \Cms\Classes\CmsException
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
            throw new ModelNotFoundException(sprintf('Category with slug %s not found.', $slug));
        }

        return Category::with($with)->findOrFail($slugMap[$slug]);
    }

    /**
     * Returns a cached map of category_ids and slug pairs.
     */
    public function getSlugMap($locale = null)
    {
        $locale = $this->getLocale($locale);

        return \Cache::rememberForever($this->mapCacheKey($locale), function () use ($locale) {
            $map = [];

            $buildSlugMap = function (?Category $parent = null, array &$map, string $base = '') use (
                &$buildSlugMap,
                $locale
            ) {
                if ($parent->rainlabTranslateInstalled()) {
                    $parent->translateContext($locale);
                }
                $slug       = trim($base . '/' . $parent->slug, '/');
                $map[$slug] = $parent->id;
                foreach ($parent->children as $child) {
                    $buildSlugMap($child, $map, $slug);
                }
            };

            $model = new Category();
            foreach ($model->getAllRoot() as $parent) {
                $buildSlugMap($parent, $map);
            }

            return $map;
        });
    }

    /**
     * Return a locale specific id map cache key.
     *
     * @param $locale
     *
     * @return string
     */
    protected function mapCacheKey($locale)
    {
        return self::MAP_CACHE_KEY . '.' . $locale;
    }

    /**
     * Return a locale specific tree cache key.
     *
     * @param $locale
     *
     * @return string
     */
    protected function treeCacheKey($locale)
    {
        return self::TREE_CACHE_KEY . '.' . $locale;
    }

    /**
     * Purge all cached category data.
     * @return void
     */
    protected function purgeCache()
    {
        foreach ($this->getLocales() as $locale) {
            Cache::forget($this->treeCacheKey($locale));
            Cache::forget($this->mapCacheKey($locale));
        }
    }

    /**
     * Pre-populate the cache.
     * @return void
     */
    protected function warmCache()
    {
        foreach ($this->getLocales() as $locale) {
            $this->getSlugMap($locale);
        }
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
        return $this->scopeAllChildren(self::newQuery(), true)
                    ->get(['id'])
                    ->pluck('id')
                    ->toArray();
    }

    /**
     * Returns the currently active locale.
     *
     * @param $locale
     *
     * @return string
     */
    protected function getLocale($locale = null): string
    {
        if ($locale !== null) {
            return $locale;
        }

        $locale = self::DEFAULT_LOCALE;
        if (class_exists(\RainLab\Translate\Classes\Translator::class)) {
            $locale = \RainLab\Translate\Classes\Translator::instance()->getLocale();
        }

        return $locale;
    }

    /**
     * Returns an array of all available locales.
     *
     * @return array
     */
    protected function getLocales(): array
    {
        $locales = [self::DEFAULT_LOCALE];
        if ($this->rainlabTranslateInstalled()) {
            $locales = \RainLab\Translate\Models\Locale::get(['code'])->pluck('code')->toArray();
        }

        return $locales;
    }

    /**
     * Check if the Translator class of RainLab.Translate is available.
     *
     * @return bool
     */
    protected function rainlabTranslateInstalled(): bool
    {
        return PluginManager::instance()->exists('RainLab.Translate');
    }
}
