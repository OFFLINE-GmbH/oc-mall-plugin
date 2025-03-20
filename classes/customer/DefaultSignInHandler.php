<?php

namespace OFFLINE\Mall\Classes\Customer;

use Auth;
use Event;
use Exception;
use Flash;
use October\Rain\Auth\AuthException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\User\Settings;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\Wishlist;
use RainLab\User\Models\Setting;
use RainLab\User\Models\User;
use RainLab\User\Models\UserLog;
use Validator;

class DefaultSignInHandler implements SignInHandler
{
    public function handle(array $data): ?User
    {
        try {
            return $this->login($data);
        } catch (ValidationException $ex) {
            throw $ex;
        } catch (AuthException $ex) {
            $error = str_contains($ex->getMessage(), 'not activated')
                ? 'not_activated'
                : 'unknown_user';

            Flash::error(trans('offline.mall::lang.components.signup.errors.' . $error));
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }

        return null;
    }

    /**
     * @throws AuthException
     * @throws ValidationException
     */
    protected function login(array $data)
    {
        $this->validate($data);

        $credentials = [
            'login'    => array_get($data, 'login'),
            'password' => array_get($data, 'password'),
        ];

        Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);
        Event::fire('mall.customer.beforeAuthenticate', [$this, $credentials]);

        // RainLab.User 3.0 compatibility
        if (class_exists(Setting::class)) {
            if (Auth::attempt(['email' => $credentials['login'], 'password' => $credentials['password']], true)) {
                $user = Auth::user();
            } else {
                throw new AuthException('rainlab.user::lang.account.invalid_login');
            }
        } else {
            $user = Auth::authenticate($credentials, true);
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            Auth::logout();

            throw new AuthException('rainlab.user::lang.account.banned');
        }

        // If the user doesn't have a Customer model it was created via the backend.
        // Make sure to add the Customer model now
        if (! $user->customer && ! $user->is_guest) {
            $customer            = new Customer();

            // RainLab.User 3.0 compatibility
            if (class_exists(Setting::class)) {
                $customer->firstname = $user->first_name;
                $customer->lastname  = $user->last_name;
            } else {
                $customer->firstname = $user->name;
                $customer->lastname  = $user->surname;
            }

            $customer->user_id   = $user->id;
            $customer->is_guest  = false;
            $customer->save();

            $user->customer = $customer;
        }

        if ($user->customer->is_guest) {
            Auth::logout();

            throw new AuthException('offline.mall::lang.components.signup.errors.user_is_guest');
        }

        Cart::transferSessionCartToCustomer($user->customer);
        Wishlist::transferToCustomer($user->customer);

        if (class_exists(UserLog::class)) {
            UserLog::createRecord($user->getKey(), UserLog::TYPE_SELF_LOGIN, [
                'user_full_name' => $user->full_name,
                'is_two_factor' => false,
            ]);
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $minPasswordLength = Settings::getMinPasswordLength();
        $rules    = [
            'login'    => 'required|email|between:6,255',
            'password' => sprintf('required|min:%d|max:255', $minPasswordLength),
        ];
        $messages = [
            'login.required'    => trans('offline.mall::lang.components.signup.errors.login.required'),
            'login.email'       => trans('offline.mall::lang.components.signup.errors.login.email'),
            'login.between'     => trans('offline.mall::lang.components.signup.errors.login.between'),
            'password.required' => trans('offline.mall::lang.components.signup.errors.password.required'),
            'password.max'      => trans('offline.mall::lang.components.signup.errors.password.max'),
        ];

        $validation = Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }
    }
}
