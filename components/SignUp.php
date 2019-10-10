<?php namespace OFFLINE\Mall\Components;

use Illuminate\Support\Facades\Session;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Models\GeneralSettings;
use RainLab\Location\Models\Country;
use RainLab\User\Models\Settings as UserSettings;

/**
 * The SignUp component displays a signup and login form
 * for user authentication.
 */
class SignUp extends MallComponent
{
    /**
     * The user has to confirm the email address.
     *
     * @var bool
     */
    public $requiresConfirmation;
    /**
     * All available countries.
     *
     * @var array
     */
    public $countries;
    /**
     * Use state field.
     *
     * @var boolean
     */
    public $useState = true;

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
                'type' => 'string',
                'name' => 'offline.mall::lang.components.signup.properties.redirect.name',
            ],
        ];
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        $this->countries = Country::getNameList();

        $this->requiresConfirmation = UserSettings::get('activate_mode') === UserSettings::ACTIVATE_USER;
        $this->useState             = GeneralSettings::get('use_state', true);
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
     * @return \Illuminate\Http\RedirectResponse|array
     * @throws \Exception
     */
    public function onSignUp()
    {
        $data                          = post();
        $data['requires_confirmation'] = $this->requiresConfirmation;

        if (app(SignUpHandler::class)->handle($data, (bool)post('as_guest'))) {
            if ($this->requiresConfirmation) {
                return ['.mall-signup-form' => $this->renderPartial($this->alias . '::confirm.htm')];
            }

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
        // Check for session redirect.
        if ($redirect = Session::pull('mall.login.redirect')) {
            return redirect()->to($redirect);
        }

        // Check for a redirect parameter specified via GET/POST.
        if ($redirect = input('redirect')) {
            return redirect()->guest($this->controller->pageUrl($redirect));
        }

        // Otherwise, use the redirect property of the component.
        if ($url = $this->property('redirect')) {
            return redirect()->to($url);
        }

        return redirect()->back();
    }
}
