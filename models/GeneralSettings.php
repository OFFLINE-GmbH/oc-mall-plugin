<?php declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\Cache;

class GeneralSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_general.yaml';

    public function afterSave()
    {
        Cache::forget('offline_mall.mysql.index.driver');
    }

    public function getPagesByComponent($component)
    {
        $theme = Theme::getActiveTheme();
        $pages = Page::listInTheme($theme, true);
        
        $cmsPages = [];
        
        foreach ($pages as $page) {
            if (!$page->hasComponent($component)) {
                continue;
            }
            $cmsPages[$page->baseFileName] = $page->title;
        }

        return count($cmsPages) < 1
            ? $this->allPages()
            : $cmsPages;
    }

    protected function allPages()
    {
        return Page
            ::listInTheme( Theme::getActiveTheme(), true)
            ->mapWithKeys(function($page) {
                return [$page->baseFileName => $page->title];
            })
            ->toArray();
    }

    public function getProductPageOptions()
    {
        return $this->getPagesByComponent('product');
    }

    public function getCategoryPageOptions()
    {
        return $this->getPagesByComponent('products');
    }

    public function getAddressPageOptions()
    {
        return $this->getPagesByComponent('addressForm');
    }

    public function getCheckoutPageOptions()
    {
        return array_merge(
            $this->getPagesByComponent('checkout'),
            $this->getPagesByComponent('quickCheckout'),
        );
    }

    public function getAccountPageOptions()
    {
        return $this->getPagesByComponent('myAccount');
    }

    public function getCartPageOptions()
    {
        return $this->getPagesByComponent('cart');
    }


}
