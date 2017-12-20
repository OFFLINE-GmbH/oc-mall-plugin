<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Country;

class SignUp extends ComponentBase
{
    use SetVars;

    public $countries;
    public $asGuest;

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
        $this->setVar('countries', Country::orderBy('name')->get());
        $this->setVar('asGuest', request()->input('asGuest', false));
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
