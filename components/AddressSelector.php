<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Validation\Rule;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use RainLab\User\Facades\Auth;
use Validator;

class AddressSelector extends ComponentBase
{
    use SetVars;

    public $cart;
    public $addresses;
    public $shippingAddress;
    public $billingAddress;
    public $type;
    public $activeAddress;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.addressSelector.details.name',
            'description' => 'offline.mall::lang.components.addressSelector.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $user = Auth::getUser();
        $this->setVar('cart', Cart::byUser($user));
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onChangeAddress()
    {
        $user = Auth::getUser();
        $type = post('type') ?? 'billing';

        $this->setVar('addresses', Address::byCustomer($user->customer)->get());
        $this->setVar('type', $type);
        $this->setVar('activeAddress', $this->cart->{$type . '_address_id'});
    }

    public function onUpdateAddress()
    {
        $user = Auth::getUser();
        $data = post();

        $rules = [
            'type' => 'required|in:billing,shipping',
            'id'   => [
                'required',
                Rule::exists('offline_mall_addresses')->where(function ($q) use ($user) {
                    $q->where('customer_id', $user->customer->id);
                }),
            ],
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $cart = Cart::byUser($user);
        $cart->{$data['type'] . '_address_id'} = $data['id'];

        $cart->save();
    }

    protected function setData()
    {
        $user = Auth::getUser();

        if ( ! $user) {
            return;
        }

        $billing  = $this->cart->billing_address_id ?? $user->customer->default_billing_address_id;
        $shipping = $this->cart->shipping_address_id ?? $user->customer->default_shipping_address_id ?? $billing;

        $addresses = Address::whereIn('id', [$billing, $shipping])->get();

        $billingAddress  = $addresses->find($billing);
        $shippingAddress = $addresses->find($shipping);

        $this->setVar('addresses', Address::byCustomer($user->customer)->get());
        $this->setVar('billingAddress', $billingAddress);
        $this->setVar('shippingAddress', $shippingAddress);
    }
}
