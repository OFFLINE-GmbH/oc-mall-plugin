<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\ShippingMethod;
use Auth;

class ShippingSelector extends ComponentBase
{
    use SetVars;

    public $cart;
    public $methods;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.shippingSelector.details.name',
            'description' => 'offline.mall::lang.components.shippingSelector.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', ShippingMethod::getAvailableByCart($this->cart));
    }
}
