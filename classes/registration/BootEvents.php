<?php

namespace OFFLINE\Mall\Classes\Registration;


use Illuminate\Support\Facades\Event;
use OFFLINE\Mall\Classes\Events\MailingEventHandler;
use OFFLINE\Mall\Classes\Search\ProductsSearchProvider;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

trait BootEvents
{
    protected function registerEvents()
    {
        $this->registerObservers();
        $this->registerGenericEvents();
        $this->registerStaticPagesEvents();
        $this->registerSiteSearchEvents();
    }

    public function registerObservers()
    {
        Product::observe(\OFFLINE\Mall\Classes\Observers\ProductObserver::class);
        Variant::observe(\OFFLINE\Mall\Classes\Observers\VariantObserver::class);
        PropertyValue::observe(\OFFLINE\Mall\Classes\Observers\PropertyValueObserver::class);
    }

    protected function registerGenericEvents()
    {
        $this->app->bind('MailingEventHandler', MailingEventHandler::class);
        Event::subscribe('MailingEventHandler');
    }

    protected function registerStaticPagesEvents()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'mall-category'       => trans('offline.mall::lang.menu_items.single_category'),
                'all-mall-categories' => trans('offline.mall::lang.menu_items.all_categories'),
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'all-mall-categories' || $type == 'mall-category') {
                return Category::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'all-mall-categories') {
                return Category::resolveCategoriesItem($item, $url, $theme);
            }
            if ($type == 'mall-category') {
                return Category::resolveCategoryItem($item, $url, $theme);
            }
        });

        // Translate slugs
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            if ($page->getBaseFileName() === GeneralSettings::get('category_page')) {
                return Category::translateParams($params, $oldLocale, $newLocale);
            }
            if ($page->getBaseFileName() === GeneralSettings::get('product_page')) {
                return Product::translateParams($params, $oldLocale, $newLocale);
            }
        });
    }

    protected function registerSiteSearchEvents()
    {
        Event::listen('offline.sitesearch.extend', function () {
            return new ProductsSearchProvider();
        });
    }
}