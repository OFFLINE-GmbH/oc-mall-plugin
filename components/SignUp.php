<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
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

    public function onRun()
    {

    }

    public function onMethodSignIn()
    {

    }

    public function onMethodSignUp()
    {

    }

    public function onSignIn()
    {
        return app(SignInHandler::class)->handle(post());
    }
}
