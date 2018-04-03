<?php namespace OFFLINE\Mall\Components;

use OFFLINE\Mall\Models\GeneralSettings;

class MyAccount extends MallComponent
{
    public $currentPage;
    public $accountPage;

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
            'orders'    => trans('offline.mall::lang.components.myAccount.pages.orders'),
            'profile'   => trans('offline.mall::lang.components.myAccount.pages.profile'),
            'addresses' => trans('offline.mall::lang.components.myAccount.pages.addresses'),
        ];
    }

    public function init()
    {
        $this->currentPage = $this->property('page');
        $this->accountPage = GeneralSettings::get('account_page');

        if ($this->currentPage === 'orders') {
            $this->addComponent(OrdersList::class, 'ordersList', []);
        } elseif ($this->currentPage === 'profile') {
            $this->addComponent(CustomerProfile::class, 'customerProfile', []);
        } elseif ($this->currentPage === 'addresses') {
            $this->addComponent(AddressList::class, 'addressList', []);
        }
    }

    public function onRun()
    {
        if ($this->currentPage === false || ! array_key_exists($this->currentPage, $this->getPageOptions())) {
            return redirect()->to($this->pageUrl('orders'));
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
