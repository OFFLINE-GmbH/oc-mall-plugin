<?php namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Controller;
use InvalidArgumentException;
use Model;
use October\Rain\Database\Traits\NestedTree;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

/**
 * Model
 */
class Category extends Model
{
    use Validation;
    use SoftDelete;
    use NestedTree;
    use Sluggable;

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
        'properties' => [
            Property::class,
            'table'    => 'offline_mall_category_property',
            'key'      => 'category_id',
            'otherKey' => 'property_id',
        ],
    ];

    public $attachOne = [
        'image' => File::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
        });
        static::deleted(function () {
            Cache::forget(self::ID_MAP_CACHE_KEY);
        });
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
        if ($type == 'all-mall-categories') {
            $result = [
                'dynamicItems' => true,
            ];
        }

        return $result;
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
        $category  = new Category();

        $pageSlug = GeneralSettings::get('category_page_slug', 'slug');
        if ($pageSlug === '') {
            $pageSlug = 'slug';
        }

        if ( ! $pageUrl = GeneralSettings::get('category_page')) {
            throw new InvalidArgumentException(
                'SnipcartShop: Please select a category page via the backend settings.'
            );
        }

        $iterator = function ($items, $baseUrl = '') use (&$iterator, &$structure, $pageUrl, $pageSlug, $url) {
            $branch = [];

            $controller = new Controller();
            foreach ($items as $item) {
                $entryUrl               = $controller->pageUrl($pageUrl, [$pageSlug => $item->slug]);
                $branchItem             = [];
                $branchItem['url']      = $entryUrl;
                $branchItem['isActive'] = $entryUrl === $url;
                $branchItem['title']    = $item->name;

                if ($item->children) {
                    $branchItem['items'] = $iterator($item->children, $item->slug);
                }

                $branch[] = $branchItem;
            }

            return $branch;
        };

        $structure['items'] = $iterator($category->getEagerRoot());

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

    public static function getByNestedSlug(string $slug)
    {
        $slug = trim($slug, ' /');

        $slugMap = (new Category)->getSlugMap();
        if ( ! isset($slugMap[$slug])) {
            return null;
        }

        return Category::findOrFail($slugMap[$slug]);
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

}
