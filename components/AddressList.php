<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Country;
use Auth;
use OFFLINE\Mall\Models\GeneralSettings;

class AddressList extends ComponentBase
{
    use HashIds;

    public $countries;
    public $addresses;
    public $addressPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.addressList.details.name',
            'description' => 'offline.mall::lang.components.addressList.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        if($user = Auth::getUser()) {
            $this->addresses   = $user->customer->addresses;
            $this->countries   = Country::get()->pluck('name', 'id');
            $this->addressPage = GeneralSettings::get('address_page');
        }
    }

    public function onDelete()
    {
        $id       = $this->decode(post('id'));
        $customer = Auth::getUser()->customer;
        $address  = Address::byCustomer($customer)->find($id);

        if ( ! $address) {
            throw new ValidationException(['id' => trans('offline.mall::lang.components.addressList.errors.address_not_found')]);
        }

        if (Address::byCustomer($customer)->count() <= 1) {
            throw new ValidationException(['id' => trans('offline.mall::lang.components.addressList.errors.cannot_delete_last_address')]);
        }

        $address->delete();
        $this->addresses = Auth::getUser()->load('customer')->customer->addresses;

        $defaultAddress = Address::byCustomer($customer)->first();

        if ($customer->default_shipping_address_id === $address->id) {
            $customer->default_shipping_address_id = $defaultAddress->id;
        }
        if ($customer->default_billing_address_id === $address->id) {
            $customer->default_billing_address_id = $defaultAddress->id;
        }

        $customer->save();

        Flash::success(trans('offline.mall::lang.components.addressList.messages.address_deleted'));

        return [
            '.mall-address-list__list' => $this->renderPartial($this->alias . '::list'),
        ];
    }
}
