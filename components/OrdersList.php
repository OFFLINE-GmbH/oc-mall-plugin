<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Models\Country;
use OFFLINE\Mall\Models\Order;

class OrdersList extends ComponentBase
{
    public $orders = [];
    public $countries = [];

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.ordersList.details.name',
            'description' => 'offline.mall::lang.components.ordersList.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $user = \Auth::getUser();
        if ( ! $user) {
            return;
        }

        $this->orders    = Order::byCustomer($user->customer)
                                ->with(['products', 'products.variant'])
                                ->orderBy('created_at', 'DESC')
                                ->get();
        $this->countries = Country::get()->pluck('name', 'id');
    }
}
