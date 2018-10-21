<?php namespace OFFLINE\Mall\Components;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;

/**
 * The SignUp component displays a signup and login form
 * for a customer to authenticate.
 */
class SignUp extends MallComponent
{
    /**
     * All available countries.
     *
     * @var Collection
     */
    public $countries;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.signup.details.name',
            'description' => 'offline.mall::lang.components.signup.details.description',
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
            'redirect' => [
                'type' => 'checkbox',
                'name' => 'offline.mall::lang.components.signup.properties.redirect.name',
            ],
        ];
    }

    /**
     * The user signs in with an existing account.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function onSignIn()
    {
        if (app(SignInHandler::class)->handle(post())) {
            return $this->redirect();
        }
    }

    /**
     * The user signs up for a new account.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function onSignUp()
    {
        if (app(SignUpHandler::class)->handle(post(), (bool)post('as_guest'))) {
            return $this->redirect();
        }
    }

    /**
     * Redirect the user after authentication.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect()
    {
        if ($url = $this->property('redirect')) {
            return redirect()->to($url);
        }

        return redirect()->back();
    }
}
