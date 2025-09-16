<?php

namespace OFFLINE\Mall\Classes\Customer;

use DB;
use Event;
use Illuminate\Support\Facades\Validator;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Classes\User\Settings;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Wishlist;
use RainLab\User\Models\Setting;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use RainLab\User\Models\UserLog;
use System\Classes\PluginManager;

class DefaultSignUpHandler implements SignUpHandler
{
    protected $asGuest;

    public function handle(array $data, bool $asGuest = false): ?User
    {
        $this->asGuest = $asGuest;

        return $this->signUp($data);
    }

    public static function rules($forSignup = true): array
    {
        $minPasswordLength = Settings::getMinPasswordLength();
        $rules = [
            'firstname'           => 'required',
            'lastname'            => 'required',
            'email'               => ['required', 'email', ($forSignup ? 'non_existing_user' : null)],
            'billing_lines'       => 'required',
            'billing_zip'         => 'required',
            'billing_city'        => 'required',
            'billing_country_id'  => 'required|exists:rainlab_location_countries,id',
            'billing_state_id'    => 'required|exists:rainlab_location_states,id',
            'shipping_lines'      => 'required_if:use_different_shipping,1',
            'shipping_zip'        => 'required_if:use_different_shipping,1',
            'shipping_city'       => 'required_if:use_different_shipping,1',
            'shipping_state_id'   => 'required_if:use_different_shipping,1|nullable|exists:rainlab_location_states,id',
            'shipping_country_id' => 'required_if:use_different_shipping,1|nullable|exists:rainlab_location_countries,id',
            'password'            => sprintf('required|min:%d|max:255', $minPasswordLength),
            'password_repeat'     => 'required|same:password',
            'terms_accepted'      => 'required',
        ];

        if ((bool)GeneralSettings::get('use_state', true) !== true) {
            unset($rules['billing_state_id'], $rules['shipping_state_id']);
        }

        Event::fire('mall.customer.extendSignupRules', [&$rules, $forSignup]);

        if (PluginManager::instance()->hasPlugin('Winter.Location')) {
            $translatedRules = array_where($rules, fn ($value, $key) => (is_string($value) && str_contains($value, 'rainlab_')));

            foreach (array_keys($translatedRules) as $rule) {
                $rules[$rule] = str_replace('rainlab_', 'winter_', $rules[$rule]);
            }
        }

        return $rules;
    }

    public static function messages(): array
    {
        $messages = [
            'email.required'          => trans('offline.mall::lang.components.signup.errors.email.required'),
            'email.email'             => trans('offline.mall::lang.components.signup.errors.email.email'),
            'email.unique'            => trans('offline.mall::lang.components.signup.errors.email.unique'),
            'email.non_existing_user' => trans('offline.mall::lang.components.signup.errors.email.non_existing_user'),

            'firstname.required'           => trans('offline.mall::lang.components.signup.errors.firstname.required'),
            'lastname.required'            => trans('offline.mall::lang.components.signup.errors.lastname.required'),
            'billing_lines.required'       => trans('offline.mall::lang.components.signup.errors.lines.required'),
            'billing_zip.required'         => trans('offline.mall::lang.components.signup.errors.zip.required'),
            'billing_city.required'        => trans('offline.mall::lang.components.signup.errors.city.required'),
            'billing_country_id.required'  => trans('offline.mall::lang.components.signup.errors.country_id.required'),
            'billing_country_id.exists'    => trans('offline.mall::lang.components.signup.errors.country_id.exists'),
            'billing_state_id.required'    => trans('offline.mall::lang.components.signup.errors.state_id.required'),
            'billing_state_id.exists'      => trans('offline.mall::lang.components.signup.errors.state_id.exists'),
            'shipping_lines.required'      => trans('offline.mall::lang.components.signup.errors.lines.required'),
            'shipping_zip.required'        => trans('offline.mall::lang.components.signup.errors.zip.required'),
            'shipping_city.required'       => trans('offline.mall::lang.components.signup.errors.city.required'),
            'shipping_country_id.required' => trans('offline.mall::lang.components.signup.errors.country_id.required'),
            'shipping_country_id.exists'   => trans('offline.mall::lang.components.signup.errors.country_id.exists'),

            'password.required' => trans('offline.mall::lang.components.signup.errors.password.required'),
            'password.min'      => trans('offline.mall::lang.components.signup.errors.password.min'),
            'password.max'      => trans('offline.mall::lang.components.signup.errors.password.max'),

            'password_repeat.required' => trans('offline.mall::lang.components.signup.errors.password_repeat.required'),
            'password_repeat.same'     => trans('offline.mall::lang.components.signup.errors.password_repeat.same'),

            'terms_accepted.required' => trans('offline.mall::lang.components.signup.errors.terms_accepted.required'),
        ];

        Event::fire('mall.customer.extendSignupMessages', [&$messages]);

        return $messages;
    }

    /**
     * @throws ValidationException
     * @return User
     */
    protected function signUp(array $data)
    {
        if ($this->asGuest) {
            $data['password'] = $data['password_repeat'] = str_random(30);
        }

        $this->validate($data);

        $requiresConfirmation = ($data['requires_confirmation'] ?? false);

        Event::fire('mall.customer.beforeSignup', [$this, $data]);

        $user = DB::transaction(function () use ($data, $requiresConfirmation) {
            $user = $this->createUser($data, $requiresConfirmation);

            $customer            = new Customer();
            $customer->firstname = $data['firstname'];
            $customer->lastname  = $data['lastname'];
            $customer->user_id   = $user->id;
            $customer->is_guest  = $this->asGuest;
            $customer->save();

            $addressData = $this->transformAddressKeys($data, 'billing');
            $fullname    = $data['firstname'] . ' ' . $data['lastname'];

            $billing = new Address();
            $billing->fill($addressData);
            $billing->name        = $addressData['address_name'] ?: $fullname;
            $billing->customer_id = $customer->id;
            $billing->save();
            $customer->default_billing_address_id = $billing->id;

            if (! empty($data['use_different_shipping'])) {
                $addressData = $this->transformAddressKeys($data, 'shipping');

                $shipping = new Address();
                $shipping->fill($addressData);
                $shipping->name        = $addressData['address_name'] ?: $fullname;
                $shipping->customer_id = $customer->id;
                $shipping->save();
                $customer->default_shipping_address_id = $shipping->id;
            } else {
                $customer->default_shipping_address_id = $billing->id;
            }

            $customer->save();

            Cart::transferSessionCartToCustomer($user->customer);
            Wishlist::transferToCustomer($user->customer);

            return $user;
        });

        // To prevent multiple guest accounts with the same email address we edit
        // the email of all existing guest accounts registered to the same email.
        $this->renameExistingGuestAccounts($data, $user);

        Event::fire('mall.customer.afterSignup', [$this, $user]);

        if (class_exists(UserLog::class)) {
            UserLog::createRecord($user->getKey(), UserLog::TYPE_NEW_USER, [
                'user_full_name' => $user->full_name,
            ]);
        }

        if ($requiresConfirmation === true) {
            return $user;
        }

        Auth::login($user, true);

        return $user;
    }

    /**
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $rules = static::rules();

        if ($this->asGuest) {
            unset($rules['password'], $rules['password_repeat']);
        }

        $messages = static::messages();

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }
    }

    /**
     * @param $data
     * @param $requiresConfirmation
     */
    protected function createUser($data, $requiresConfirmation)
    {
        $data['name']                  = $data['firstname'];
        $data['surname']               = $data['lastname'];
        $data['email']                 = $data['email'];
        $data['password']              = $data['password'];
        $data['password_confirmation'] = $data['password_repeat'];
        $data['is_guest']              = $this->asGuest;

        // RainLab.User 3.0
        if (class_exists(Setting::class)) {
            $data['first_name'] = $data['firstname'];
            $data['last_name']  = $data['lastname'];

            unset($data['name']);
            unset($data['surname']);
        }
        
        $user = Auth::register($data, ! $requiresConfirmation);

        if ($this->asGuest && $user && $group = UserGroup::getGuestGroup()) {
            $user->groups()->sync($group);
        } else {
            $user->groups()->sync([]);
        }

        return $user;
    }

    protected function transformAddressKeys(array $data, string $type): array
    {
        return collect($data)->mapWithKeys(function ($value, $key) use ($type) {
            if (starts_with($key, $type)) {
                $newKey = str_replace($type . '_', '', $key);

                return [$newKey => $value];
            }

            return [];
        })->toArray();
    }

    protected function renameExistingGuestAccounts(array $data, $user)
    {
        // Add a "mall-guest_2021-05-31_075100" suffix to the already registered email.
        $parts = explode('@', $data['email']);
        $suffix = 'mall-guest_' . date('Y-m-d_His');

        $newEmail = sprintf('%s+%s@%s', $parts[0], $suffix, $parts[1]);

        User::where('id', '<>', $user->id)
            ->where('email', $data['email'])
            ->whereHas('customer', function ($q) {
                $q->where('is_guest', 1);
            })
            ->update(['email' => $newEmail, 'username' => $newEmail]);
    }
}
