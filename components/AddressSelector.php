<?php namespace OFFLINE\Mall\Components;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use RainLab\User\Facades\Auth;
use Validator;

/**
 * The AddressSelector component displays a dropdown
 * to select an address.
 */
class AddressSelector extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var Cart
     */
    public $cart;
    /**
     * All the user's addresses.
     *
     * @var Collection
     */
    public $addresses;
    /**
     * The currently active address.
     * This will be displayed as a full string representation.
     *
     * @var Address
     */
    public $address;
    /**
     * The type of the address (billing, shipping).
     *
     * @var string
     */
    public $type;
    /**
     * The currently active Address in the selection dropdown.
     *
     * @var Address
     */
    public $activeAddress;
    /**
     * The name of the address edit page.
     *
     * @var string
     */
    public $addressPage;
    /**
     * The redirect parameter for the address edit form.
     *
     * @var string
     */
    public $redirect = 'checkout';

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.addressSelector.details.name',
            'description' => 'offline.mall::lang.components.addressSelector.details.description',
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
            'type' => [
                'label' => 'Type',
                'type'  => 'dropdown',
            ],
            'redirect' => [
                'label' => 'Redirect',
                'type'  => 'string',
                'default' => 'checkout'
            ],
        ];
    }

    /**
     * Options array for the type dropdown.
     *
     * @return array
     */
    public function getTypeOptions()
    {
        return [
            'shipping' => trans('offline.mall::lang.order.shipping_address'),
            'billing'  => trans('offline.mall::lang.order.billing_address'),
        ];
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        $user = Auth::getUser();
        $this->setVar('cart', Cart::byUser($user));
        $this->setVar('redirect', $this->property('redirect'));
    }

    /**
     * The component is executed.
     *
     * @return RedirectResponse
     */
    public function onRun()
    {
        $this->setData();

        if (Auth::getUser() && $this->addresses->count() < 1) {
            Flash::warning(trans('offline.mall::frontend.flash.missing_address'));

            $url = $this->controller->pageUrl($this->addressPage, [
                'address'  => 'new',
                'redirect' => 'payment',
                'set'      => 'both',
            ]);

            return response()->redirectTo($url);
        }
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return bool
     */
    protected function setData()
    {
        $user = Auth::getUser();
        if ( ! $user) {
            return;
        }

        if ( ! $user->customer) {
            logger()->warning('User account without customer relation found.', ['user' => $user]);
            Auth::logout();
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

    /**
     * The user wants to select another address.
     *
     * Display a dropdown of all available addresses.
     *
     * @return void
     */
    public function onChangeAddress()
    {
        $user = Auth::getUser();
        $this->setData();

        $this->setVar('addresses', Address::byCustomer($user->customer)->get());
        $this->setVar('activeAddress', $this->cart->{$this->type . '_address_id'});
    }

    /**
     * The user selected a new address.
     *
     * @return array
     * @throws ValidationException
     */
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
}
