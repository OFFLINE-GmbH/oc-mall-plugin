<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;

class MyAccount extends ComponentBase
{
    public $currentPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.myAccount.details.name',
            'description' => 'offline.mall::lang.components.myAccount.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'page' => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.myAccount.properties.page.title',
            ],
        ];
    }

    public function getPageOptions()
    {
        return [
            'orders'  => trans('offline.mall::lang.components.myAccount.pages.orders'),
            'profile' => trans('offline.mall::lang.components.myAccount.pages.profile'),
        ];
    }

    public function onRun()
    {
        $this->currentPage = $this->property('page');
        if ($this->currentPage === false || ! array_key_exists($this->currentPage, $this->getPageOptions())) {
            return redirect()->to($this->pageUrl('orders'));
        }

        if ($this->currentPage === 'orders') {
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
