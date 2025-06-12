<?php

namespace OFFLINE\Mall\Models;

use Cms\Classes\Page;
use Illuminate\Support\Facades\Cache;
use Model;
use Cms\Classes\Theme;
use Session;

class GeneralSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_general.yaml';

    public function afterSave()
    {
        Cache::forget('offline_mall.mysql.index.driver');
    }

    /**
     * Get Pages by CMS Component
     * @param string $component
     * @param bool $allOnEmpty
     * @return array
     */
    public function getPagesByComponent(string $component, bool $allOnEmpty = true)
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

        if (count($cmsPages) < 1) {
            return $allOnEmpty ? $this->allPages() : [];
        } else {
            return $cmsPages;
        }
    }

    /**
     * Return all CMS Pages
     * @return array
     */
    protected function allPages()
    {
        return Page
            ::listInTheme( Theme::getActiveTheme(), true)
            ->mapWithKeys(function($page) {
                return [$page->baseFileName => $page->title];
            })
            ->toArray();
    }

    /**
     * Return CMS Pages with [product] component
     * @return array
     */
    public function getProductPageOptions()
    {
        return $this->getPagesByComponent('product');
    }

    /**
     * Return CMS Pages with [products] component
     * @return array
     */
    public function getCategoryPageOptions()
    {
        return $this->getPagesByComponent('products');
    }

    /**
     * Return CMS Pages with [addressForm] component
     * @return array
     */
    public function getAddressPageOptions()
    {
        return $this->getPagesByComponent('addressForm');
    }

    /**
     * Return CMS Pages with [checkout] and [quickCheckout] component
     * @return array
     */
    public function getCheckoutPageOptions()
    {
        $result = array_merge(
            $this->getPagesByComponent('checkout', false),
            $this->getPagesByComponent('quickCheckout', false),
        );

        return empty($result) ? $this->allPages() : $result;
    }

    /**
     * Return CMS Pages with [myAccount] component
     * @return array
     */
    public function getAccountPageOptions()
    {
        return $this->getPagesByComponent('myAccount');
    }

    /**
     * Return CMS Pages with [cart] component
     * @return array
     */
    public function getCartPageOptions()
    {
        return $this->getPagesByComponent('cart');
    }
}
