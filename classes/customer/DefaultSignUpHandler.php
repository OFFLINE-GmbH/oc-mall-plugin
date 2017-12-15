<?php

namespace OFFLINE\Mall\Classes\Customer;

use DB;
use Event;
use Flash;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Customer;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Redirect;
use Validator;

class DefaultSignUpHandler implements SignUpHandler
{
    protected $asGuest;

    public function handle(array $data, bool $asGuest = false): ?User
    {
        $this->asGuest = $asGuest;

        return $this->signUp($data);
    }

    /**
     * @throws ValidationException
     */
    protected function signUp(array $data)
    {
        if ($this->asGuest) {
            $data['password'] = $data['password_repeat'] = str_random(30);
        }

        $this->validate($data);

        Event::fire('mall.user.beforeSignup', [$this, $data]);

        $user = DB::transaction(function () use ($data) {

            $user = $this->createUser($data);

            $customer           = new Customer();
            $customer->name     = $data['name'];
            $customer->user_id  = $user->id;
            $customer->is_guest = $this->asGuest;
            $customer->save();

            $address = new Address();
            $address->fill($data);
            $address->customer_id = $customer->id;
            $address->save();

            $customer->default_shipping_address_id = $address->id;
            $customer->default_billing_address_id  = $address->id;
            $customer->save();

            return $user;
        });

        Event::fire('mall.user.afterSignup', [$this, $data]);

        $credentials = [
            'login'    => array_get($data, 'email'),
            'password' => array_get($data, 'password'),
        ];

        return Auth::authenticate($credentials, true);
    }

    /**
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $rules = [
            'email'           => 'required|email|unique:users,email',
            'name'            => 'required',
            'lines'           => 'required',
            'zip'             => 'required',
            'city'            => 'required',
            'country_id'      => 'required|exists:offline_mall_countries,id',
            'password'        => 'required|min:8|max:255',
            'password_repeat' => 'required|same:password',
        ];

        if ($this->asGuest) {
            unset($rules['password']);
            unset($rules['password_repeat']);
        }

        $messages = [
            'email.required' => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'    => trans('offline.mall::lang.components.signup.errors.email.email'),
            'email.unique'   => trans('offline.mall::lang.components.signup.errors.email.unique'),

            'name.required'       => trans('offline.mall::lang.components.signup.errors.name.required'),
            'lines.required'      => trans('offline.mall::lang.components.signup.errors.lines.required'),
            'zip.required'        => trans('offline.mall::lang.components.signup.errors.zip.required'),
            'city.required'       => trans('offline.mall::lang.components.signup.errors.city.required'),
            'country_id.required' => trans('offline.mall::lang.components.signup.errors.country_id.required'),
            'country_id.exists'   => trans('offline.mall::lang.components.signup.errors.country_id.exists'),

            'password.required' => trans('offline.mall::lang.components.signup.errors.password.required'),
            'password.min'      => trans('offline.mall::lang.components.signup.errors.password.min'),
            'password.max'      => trans('offline.mall::lang.components.signup.errors.password.max'),

            'password_repeat.required' => trans('offline.mall::lang.components.signup.errors.password_repeat.required'),
            'password_repeat.same'     => trans('offline.mall::lang.components.signup.errors.password_repeat.same'),
        ];

        $validation = Validator::make($data, $rules, $messages);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }
    }

    protected function createUser($data): User
    {
        $data = [
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'password'              => $data['password'],
            'password_confirmation' => $data['password_repeat'],
        ];

        $user = Auth::register($data, true);
        if ($this->asGuest && $user && $group = UserGroup::getGuestGroup()) {
            $user->groups()->add($group);
        }

        return $user;
    }

}