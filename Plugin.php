<?php namespace OFFLINE\Mall;

use OFFLINE\Mall\Models\Category;
use System\Classes\PluginBase;
use Event;

class Plugin extends PluginBase
{
    public $require = ['RainLab.Translate', 'RainLab.User', 'OFFLINE.Cashier'];

    public function boot()
    {
        $this->registerStaticPagesEvents();
    }

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    protected function registerStaticPagesEvents()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'all-mall-categories' => trans('offline.mall::lang.menu_items.all_categories'),
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'all-mall-categories') {
                return Category::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'all-mall-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
        });
    }
}
