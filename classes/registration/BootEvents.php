<?php

namespace OFFLINE\Mall\Classes\Registration;

use Event;
use OFFLINE\Mall\Classes\Events\MailingEventHandler;
use OFFLINE\Mall\Classes\Search\ProductsSearchProvider;
use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Models\Wishlist;

trait BootEvents
{
    public function registerObservers()
    {
        Product::observe(\OFFLINE\Mall\Classes\Observers\ProductObserver::class);
        Variant::observe(\OFFLINE\Mall\Classes\Observers\VariantObserver::class);
        Brand::observe(\OFFLINE\Mall\Classes\Observers\BrandObserver::class);
        PropertyValue::observe(\OFFLINE\Mall\Classes\Observers\PropertyValueObserver::class);
        ProductPrice::observe(\OFFLINE\Mall\Classes\Observers\ProductPriceObserver::class);
    }

    protected function registerEvents()
    {
        $this->registerObservers();
        $this->registerGenericEvents();
        $this->registerStaticPagesEvents();
        $this->registerSiteSearchEvents();
        $this->registerGdprEvents();
    }

    protected function registerGenericEvents()
    {
        $this->app->bind(MailingEventHandler::class, fn () => new MailingEventHandler());

        $this->app['events']->subscribe(MailingEventHandler::class);
    }

    protected function registerStaticPagesEvents()
    {
        $listTypes = function () {
            return [
                'mall-category' => '[OFFLINE.Mall] ' . trans('offline.mall::lang.menu_items.single_category'),
                'all-mall-categories' => '[OFFLINE.Mall] ' . trans('offline.mall::lang.menu_items.all_categories'),
                'all-mall-products' => '[OFFLINE.Mall] ' . trans('offline.mall::lang.menu_items.all_products'),
                'all-mall-variants' => '[OFFLINE.Mall] ' . trans('offline.mall::lang.menu_items.all_variants'),
            ];
        };

        $getTypeInfo = function ($type) {
            if ($type === 'all-mall-categories' || $type === 'mall-category') {
                return Category::getMenuTypeInfo($type);
            }

            if ($type === 'all-mall-products' || $type === 'all-mall-variants') {
                return [
                    'dynamicItems' => true,
                ];
            }

            return null;
        };

        $resolveItem = function ($type, $item, $url, $theme) {
            if ($type === 'all-mall-categories') {
                return Category::resolveCategoriesItem($item, $url, $theme);
            }

            if ($type === 'mall-category') {
                return Category::resolveCategoryItem($item, $url, $theme);
            }

            if ($type === 'all-mall-products') {
                return Product::resolveItem($item, $url, $theme);
            }

            if ($type === 'all-mall-variants') {
                return Variant::resolveItem($item, $url, $theme);
            }

            return null;
        };

        // RainLab.Pages
        Event::listen('pages.menuitem.listTypes', $listTypes);
        Event::listen('pages.menuitem.getTypeInfo', $getTypeInfo);
        Event::listen('pages.menuitem.resolveItem', $resolveItem);

        // October 3 CMS Module
        Event::listen('cms.pageLookup.listTypes', $listTypes);
        Event::listen('cms.pageLookup.getTypeInfo', $getTypeInfo);
        Event::listen('cms.pageLookup.resolveItem', $resolveItem);

        // Translate slugs
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            if ($page->getBaseFileName() === GeneralSettings::get('category_page')) {
                return Category::translateParams($params, $oldLocale, $newLocale);
            }

            if ($page->getBaseFileName() === GeneralSettings::get('product_page')) {
                return Product::translateParams($params, $oldLocale, $newLocale);
            }
        });

        // Translate slugs October 3 CMS
        Event::listen('cms.sitePicker.overrideParams', function ($page, $params, $currentSite, $proposedSite) {
            if ($page->getBaseFileName() === GeneralSettings::get('category_page')) {
                return Category::translateParams($params, $currentSite->hard_locale, $proposedSite->hard_locale);
            }

            if ($page->getBaseFileName() === GeneralSettings::get('product_page')) {
                return Product::translateParams($params, $currentSite->hard_locale, $proposedSite->hard_locale);
            }
        });
    }

    protected function registerSiteSearchEvents()
    {
        Event::listen('offline.sitesearch.extend', fn () => new ProductsSearchProvider());
    }

    protected function registerGdprEvents()
    {
        Event::listen('offline.gdpr::cleanup.register', function () {
            return [
                'id'     => 'oc-mall-plugin',
                'label'  => 'OFFLINE Mall',
                'models' => [
                    [
                        'label'   => 'Customers',
                        'comment' => 'Delete inactive customer accounts (based on last login date)',
                        'class'   => Customer::class,
                    ],
                    [
                        'label'   => 'Orders',
                        'comment' => 'Delete completed orders',
                        'class'   => Order::class,
                    ],
                    [
                        'label'   => 'Carts',
                        'comment' => 'Delete abandoned shopping carts',
                        'class'   => Cart::class,
                    ],
                    [
                        'label'   => 'Wishlists',
                        'comment' => 'Delete old wishlists of unregistered users',
                        'class'   => Wishlist::class,
                    ],
                ],
            ];
        });
    }
}
