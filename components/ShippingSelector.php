<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\ShippingMethod;
use Validator;

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
        $this->setData();
    }

    public function onSelect()
    {
        $this->setData();

        $v = Validator::make(post(), [
            'id' => 'required|exists:offline_mall_shipping_methods,id',
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        if ( ! $this->methods || ! $this->methods->pluck('id')->contains(post('id'))) {
            throw new ValidationException(['id' => trans('offline.mall::lang.components.shippingSelector.errors.unavailable')]);
        }

        $this->cart->shipping_method_id = post('id');
        $this->cart->save();

        // Reload the cart to make sure everything is up to date.
        $this->setData();
    }

    protected function setData()
    {
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', ShippingMethod::getAvailableByCart($this->cart));
    }
}
