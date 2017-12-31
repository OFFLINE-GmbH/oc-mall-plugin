<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\PaymentMethod;
use Validator;

class PaymentMethodSelector extends ComponentBase
{
    use SetVars;

    public $cart;
    public $activeMethod;
    public $methods;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.paymentMethodSelector.details.name',
            'description' => 'offline.mall::lang.components.paymentMethodSelector.details.description',
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

    public function onChangeMethod()
    {
        $this->setData();

        $rules = [
            'id' => 'required|exists:offline_mall_payment_methods,id',
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $id = post('id');
        $this->cart->setPaymentMethod($id);
        $this->setVar('activeMethod', $id);
    }

    protected function setData()
    {
        $this->setVar('cart', Cart::byUser(Auth::getUser()));
        $this->setVar('methods', PaymentMethod::orderBy('sort_order', 'ASC')->get());
        $this->setVar('activeMethod', $this->cart->payment_method_id);
    }
}
