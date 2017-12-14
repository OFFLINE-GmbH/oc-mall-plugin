<?php

namespace OFFLINE\Mall\Classes\Customer;

use Event;
use Exception;
use Flash;
use October\Rain\Auth\AuthException;
use October\Rain\Exception\ValidationException;
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

        return Auth::authenticate($credentials, true);
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