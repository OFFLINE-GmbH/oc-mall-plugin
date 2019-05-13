<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;

class EnhancedEcommerceAnalytics extends ComponentBase
{
    public $products;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.enhancedEcommerceAnalytics.details.name',
            'description' => 'offline.mall::lang.components.enhancedEcommerceAnalytics.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
    }
}
