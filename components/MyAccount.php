<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Models\GeneralSettings;
use RainLab\User\Facades\Auth;

/**
 * The MyAccount component displays an overview of a customer's account.
 */
class MyAccount extends MallComponent
{
    /**
     * The currently active sub-page.
     *
     * @var string
     */
    public $currentPage;
    /**
     * The name of the account page.
     *
     * @var string
     */
    public $accountPage;
    /**
     * Store any redirects to execute when the component loads.
     *
     * @var RedirectResponse
     */
    public $redirect;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.myAccount.details.name',
            'description' => 'offline.mall::lang.components.myAccount.details.description',
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
            'page' => [
                'type'  => 'dropdown',
                'title' => 'offline.mall::lang.components.myAccount.properties.page.title',
            ],
        ];
    }

    /**
     * Options array for the page dropdown.
     *
     * @return array
     */
    public function getPageOptions()
    {
        return [
            'orders'    => trans('offline.mall::lang.components.myAccount.pages.orders'),
            'profile'   => trans('offline.mall::lang.components.myAccount.pages.profile'),
            'addresses' => trans('offline.mall::lang.components.myAccount.pages.addresses'),
        ];
    }

    /**
     * The component is initialized.
     *
     * All child components get added.
     *
     * @return void
     */
    public function init()
    {
        $this->currentPage = $this->property('page');
        $this->accountPage = GeneralSettings::get('account_page');

        if ($this->currentPage === 'orders') {
            $this->addComponent(OrdersList::class, 'ordersList', []);
        } elseif ($this->currentPage === 'profile') {
            $this->addComponent(CustomerProfile::class, 'customerProfile', []);
        } elseif ($this->currentPage === 'addresses') {
            $this->addComponent(AddressList::class, 'addressList', []);
        } elseif ($this->currentPage === 'confirmation') {
            $this->redirect = $this->handleConfirmation();
        }
    }

    /**
     * The component is executed.
     *
     * @return RedirectResponse?
     */
    public function onRun()
    {
        if ($this->redirect) {
            return $this->redirect;
        }

        if ( ! $this->isValidPage()) {
            return $this->exitRedirect();
        }
    }

    /**
     * Return the URL to a specific sub-page.
     *
     * @param       $page
     * @param array $params
     *
     * @return string
     */
    public function pageUrl($page, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['page' => $page])
        );
    }

    /**
     * Handle the user account confirmation link.
     */
    protected function handleConfirmation()
    {
        try {
            $code = request()->get('code');

            $error = [
                'code' => trans('offline.mall::frontend.account.confirmation.error'),
            ];

            $parts = explode('!', $code);
            if (count($parts) !== 2) {
                throw new ValidationException([$error]);
            }

            list($userId, $code) = $parts;

            if (trim($userId) === '' || trim($code) === '') {
                throw new ValidationException($error);
            }

            if ( ! $user = Auth::findUserById($userId)) {
                throw new ValidationException($error);
            }

            if ( ! $user->attemptActivation($code)) {
                throw new ValidationException($error);
            }

            Flash::success(trans('rainlab.user::lang.account.success_activation'));

            Auth::login($user);

            return $this->cartRedirect();
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }

        return $this->exitRedirect();
    }

    /**
     * Check if the visited page is valid.
     *
     * @return bool
     */
    protected function isValidPage(): bool
    {
        return $this->currentPage !== false
            && array_key_exists($this->currentPage, $this->getPageOptions());
    }

    /**
     * Redirect in case of error.
     *
     * @return RedirectResponse
     */
    private function exitRedirect()
    {
        return redirect()->to($this->pageUrl('orders'));
    }

    /**
     * Redirect to cart page.
     *
     * @return RedirectResponse
     * @throws \Cms\Classes\CmsException
     */
    private function cartRedirect()
    {
        $url = (new Controller())->pageUrl(GeneralSettings::get('cart_page'));

        return redirect()->to($url);
    }
}
