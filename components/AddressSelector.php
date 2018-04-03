<?php namespace OFFLINE\Mall\Components;

use Illuminate\Validation\Rule;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use RainLab\User\Facades\Auth;
use Validator;

class AddressSelector extends MallComponent
{
    public $cart;
    public $addresses;
    public $address;
    public $type;
    public $activeAddress;
    public $addressPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.addressSelector.details.name',
            'description' => 'offline.mall::lang.components.addressSelector.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'label' => 'Type',
                'type'  => 'dropdown',
            ],
        ];
    }

    public function getTypeOptions()
    {
        return [
            'shipping' => trans('offline.mall::lang.order.shipping_address'),
            'billing'  => trans('offline.mall::lang.order.billing_address'),
        ];
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
        $this->setData();

        $this->setVar('addresses', Address::byCustomer($user->customer)->get());
        $this->setVar('activeAddress', $this->cart->{$this->type . '_address_id'});
    }

    public function onUpdateAddress()
    {
        $user = Auth::getUser();
        $this->setData();

        $data  = post();
        $rules = [
            'id' => [
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

        $col = $this->type . '_address_id';

        $cart         = Cart::byUser($user);
        $cart->{$col} = $data['id'];
        $cart->save();

        $selector = '.mall-address-selector--' . $this->type;
        $partial  = $this->alias . '::selector';

        $this->cart = $cart;
        $this->setData();

        return [$selector => $this->renderPartial($partial)];
    }

    protected function setData()
    {
        $user = Auth::getUser();
        if ( ! $user) {
            return;
        }

        $this->setVar('type', $this->property('type'));

        if ($this->type === 'billing') {
            $address = $this->cart->billing_address_id ?? $user->customer->default_billing_address_id;
        } else {
            $address = $this->cart->shipping_address_id ?? $user->customer->default_shipping_address_id;
        }

        $addresses = Address::byCustomer($user->customer)->get();
        $address   = $addresses->find($address);

        $this->setVar('addresses', $addresses);
        $this->setVar('address', $address);
        $this->setVar('addressPage', GeneralSettings::get('address_page'));
    }
}
