<?php

namespace OFFLINE\Mall\Classes\Traits\Category;

use Cache;
use Cms\Classes\Controller;
use Cms\Classes\Page;
use InvalidArgumentException;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;

trait MenuItems
{
    /**
     * @param $item
     * @param $url
     * @param $theme
     *
     * @return array
     * @throws \Cms\Classes\CmsException
     */
    public static function resolveCategoryItem($item, $url, $theme)
    {
        $category = self::find($item->reference);
        if ( ! $category) {
            return;
        }

        // Replace this menu item with its products.
        if ($item->replace) {
            $page = GeneralSettings::get('product_page', 'product');
            if ( ! Page::loadCached($theme, $page)) {
                return;
            }

            $controller = Controller::getController() ?: new Controller;

            $items = $category->products
                ->map(function (Product $product) use ($page, $url, $controller) {

                    $pageUrl = $controller->pageUrl($page, ['slug' => $product->slug], false);

                    return [
                        'title'    => $product->name,
                        'url'      => $pageUrl,
                        'mtime'    => $product->updated_at,
                        'isActive' => starts_with($url, $pageUrl),
                    ];
                })->toArray();

            return [
                'items' => $items,
            ];
        }

        return self::getMenuItem($category, $url);
    }

    /**
     * Purge all cached category data.
     * @return void
     */
    public function purgeCache()
    {
        foreach ($this->getLocales() as $locale) {
            Cache::forget($this->treeCacheKey($locale));
            Cache::forget($this->mapCacheKey($locale));
        }
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
            return $category->setActiveMenuItem(
                Cache::get($category->treeCacheKey($locale)
                ), $url);
        }

        $structure = [];
        $iterator  = function ($items, $baseUrl = '') use (&$iterator, &$structure, $url, $locale) {
            $branch = [];
            foreach ($items as $item) {
                $branchItem = self::getMenuItem($item, $url);
                $item->setTranslateContext($locale);
                if ($item->children->count() > 0) {
                    $branchItem['items'] = $iterator($item->children, $item->slug);
                }
                $branch[] = $branchItem;
            }

            return $branch;
        };

        $structure['items'] = $iterator($category->getEagerRoot());

        Cache::forever($category->treeCacheKey($locale), $structure);

        $structure = $category->setActiveMenuItem($structure, $url);

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
        $entryUrl   = $controller->pageUrl($pageUrl, ['slug' => $item->nestedSlug], false);

        $result             = [];
        $result['url']      = $entryUrl;
        $result['mtime']    = $item->updated_at;
        $result['title']    = $item->name;
        $result['code']     = $item->code;
        $result['isActive'] = $url === $entryUrl;

        return $result;
    }

    public static function getMenuTypeInfo($type)
    {
        $result = [];
        if ($type === 'mall-category') {
            $result = [
                'references'   => Category::listSubCategoryOptions(),
                'dynamicItems' => true,
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
     * Return a locale specific id map cache key.
     *
     * @param $locale
     *
     * @return string
     */
    protected function mapCacheKey($locale)
    {
        return Category::MAP_CACHE_KEY . '.' . $locale;
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
        return Category::TREE_CACHE_KEY . '.' . $locale;
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
     * Mark the currently active menu item as isActive.
     *
     * The active menu item is set manually since the whole tree structure
     * gets cached. The active menu item will change on a per-page basis though.
     *
     * @param $items
     * @param $url
     *
     * @return array
     */
    protected function setActiveMenuItem($items, $url)
    {
        $iterator = function ($items, $url) use (&$iterator) {
            foreach ($items as &$item) {
                $item['isActive'] = $item['url'] === $url;
                if (isset($item['items']) && count($item['items']) > 0) {
                    $item['items'] = $iterator($item['items'], $url);
                }
            }

            return $items;
        };

        $items['items'] = $iterator($items['items'], $url);

        return $items;
    }
}
