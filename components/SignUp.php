<?php namespace OFFLINE\Mall\Components;

use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Models\Country;

class SignUp extends MallComponent
{
    public $countries;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.signup.details.name',
            'description' => 'offline.mall::lang.components.signup.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'type' => 'checkbox',
                'name' => 'offline.mall::lang.components.signup.properties.redirect.name',
            ],
        ];
    }

    public function init()
    {
        $this->setVar('countries', Country::get());
    }

    public function onSignIn()
    {
        if (app(SignInHandler::class)->handle(post())) {
            return $this->redirect();
        }
    }

    public function onSignUp()
    {
        if (app(SignUpHandler::class)->handle(post(), (bool)post('as_guest'))) {
            return $this->redirect();
        }
    }

    protected function redirect()
    {
        if ($url = $this->property('redirect')) {
            return redirect()->to($url);
        }

        return redirect()->back();
    }
}
