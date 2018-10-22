<?php namespace OFFLINE\Mall\Components;

use Illuminate\Http\RedirectResponse;
use OFFLINE\Mall\Models\GeneralSettings;

/**
 * The MyAccount component displays an overview of a customer's account.
 */
class MyAccount extends MallComponent
{
    /**
     * The currently active sub-page.
     *
     * @var string
     */
    public $currentPage;
    /**
     * The name of the account page.
     *
     * @var string
     */
    public $accountPage;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.myAccount.details.name',
            'description' => 'offline.mall::lang.components.myAccount.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'page' => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.myAccount.properties.page.title',
            ],
        ];
    }

    /**
     * Options array for the page dropdown.
     *
     * @return array
     */
    public function getPageOptions()
    {
        return [
            'orders'    => trans('offline.mall::lang.components.myAccount.pages.orders'),
            'profile'   => trans('offline.mall::lang.components.myAccount.pages.profile'),
            'addresses' => trans('offline.mall::lang.components.myAccount.pages.addresses'),
        ];
    }

    /**
     * The component is initialized.
     *
     * All child components get added.
     *
     * @return void
     */
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

    /**
     * The component is executed.
     *
     * @return RedirectResponse?
     */
    public function onRun()
    {
        if ($this->currentPage === false || ! array_key_exists($this->currentPage, $this->getPageOptions())) {
            return redirect()->to($this->pageUrl('orders'));
        }
    }

    /**
     * Return the URL to a specific sub-page.
     *
     * @param       $page
     * @param array $params
     *
     * @return string
     */
    public function pageUrl($page, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['page' => $page])
        );
    }
}
