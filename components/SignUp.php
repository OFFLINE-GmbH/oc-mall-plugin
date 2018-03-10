<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Traits\SetVars;

class SignUp extends ComponentBase
{
    use SetVars;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.signup.details.name',
            'description' => 'offline.mall::lang.components.signup.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onSignIn()
    {
        if (app(SignInHandler::class)->handle(post())) {
            return Redirect::back();
        }
    }

    public function onSignUp()
    {
        if (app(SignUpHandler::class)->handle(post(), (bool)post('as_guest'))) {
            return Redirect::back();
        }
    }
}
