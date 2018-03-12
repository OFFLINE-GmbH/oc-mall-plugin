<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;

class CustomerProfile extends ComponentBase
{
    public $currentPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.customerProfile.details.name',
            'description' => 'offline.mall::lang.components.customerProfile.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'page' => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.customerProfile.properties.page.title',
            ],
        ];
    }

    public function getPageOptions()
    {
        return [
            'orders'  => trans('offline.mall::lang.components.customerProfile.pages.orders'),
            'profile' => trans('offline.mall::lang.components.customerProfile.pages.profile'),
        ];
    }

    public function onRun()
    {
        $this->currentPage = $this->property('page');
        if($this->currentPage === 'orders') {
            $this->addComponent(OrdersList::class, 'ordersList', []);
        }
    }

    public function pageUrl($page, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['page' => $page])
        );
    }
}
