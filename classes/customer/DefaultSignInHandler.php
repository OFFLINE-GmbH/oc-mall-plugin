<?php

namespace OFFLINE\Mall\Classes\Customer;

use Event;
use Exception;
use Flash;
use October\Rain\Auth\AuthException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Cart;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\User;
use Redirect;
use Validator;

class DefaultSignInHandler implements SignInHandler
{
    public function handle(array $data): ?User
    {
        try {
            return $this->login($data);
        } catch (AuthException $ex) {
            Flash::error(trans('offline.mall::lang.components.signup.errors.unknown_user'));
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
        Event::fire('mall.user.beforeAuthenticate', [$this, $credentials]);

        $user = Auth::authenticate($credentials, true);
        if ($user->isBanned()) {
            Auth::logout();
            throw new AuthException('rainlab.user::lang.account.banned');
        }

        if ($user->customer->is_guest) {
            Auth::logout();
            throw new AuthException('offline.mall::lang.components.signup.errors.user_is_guest');
        }

        Cart::transferToCustomer($user->customer);

        return $user;
    }

    /**
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $rules    = [
            'login'    => 'required|email|between:6,255',
            'password' => 'required|min:8|max:255',
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