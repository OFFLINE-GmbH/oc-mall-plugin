<?php

namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Support\Collection;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentRedirector;
use OFFLINE\Mall\Classes\Payments\PaymentService;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\User;
use RainLab\Location\Models\Country;
use Validator;

/**
 * The QuickCheckout component provides a checkout process on a single page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuickCheckout extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var Cart
     */
    public $cart;
    /**
     * The currently selected payment method.
     *
     * @var Collection<PaymentMethod>
     */
    public $paymentMethods;
    /**
     * All available CustomerPaymentMethods
     * @var Collection
     */
    public $customerPaymentMethods;
    /**
     * All available shipping methods.
     *
     * @var Collection<ShippingMethod>
     */
    public $shippingMethods;
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
     * Name of the CMS page that hosts the signUp component.
     *
     * @var string
     */
    public $loginPage = 'login';
    /**
     * Current page URL.
     *
     * @var string
     */
    public $currentPage;
    /**
     * Account page.
     *
     * @var string
     */
    public $accountPage;
    /**
     * The current user.
     *
     * @var User
     */
    public $user;
    /**
     * The currently active step.
     *
     * @var string
     */
    public $step;
    /**
     * The order that was created during checkout.
     *
     * @var Order
     */
    public $order;
    /**
     * The error massage received from the PaymentProvider.
     *
     * @var string
     */
    public $paymentError;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name' => 'offline.mall::lang.components.quickCheckout.details.name',
            'description' => 'offline.mall::lang.components.quickCheckout.details.description',
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
            'loginPage' => [
                'title' => 'Name of the login page',
                'default' => 'login',
            ],
            'step' => [
                'type' => 'dropdown',
                'name' => 'offline.mall::lang.components.checkout.properties.step.name',
                'default' => 'overview',
            ],
        ];
    }

    /**
     * Options array for the step dropdown.
     *
     * @return array
     */
    public function getStepOptions()
    {
        return [
            'overview' => trans('offline.mall::lang.components.checkout.steps.confirm'),
            'failed' => trans('offline.mall::lang.components.checkout.steps.failed'),
            'cancelled' => trans('offline.mall::lang.components.checkout.steps.cancelled'),
            'done' => trans('offline.mall::lang.components.checkout.steps.done'),
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
        $this->step = $this->property('step', 'overview');
        // The default step is "overview". Since this component shows all steps on one screen,
        // the "payment" step can be redirected to the overview as well. The "payment" step is used
        // in case of subsequent payments for a previously failed order.
        if ( ! $this->step) {
            $this->step = 'overview';
        }
        if ($this->step === 'overview') {
            $this->addComponent(AddressSelector::class, 'billingAddressSelector', ['type' => 'billing', 'redirect' => 'quickCheckout']);
            $this->addComponent(AddressSelector::class, 'shippingAddressSelector', ['type' => 'shipping', 'redirect' => 'quickCheckout']);
        } elseif ($this->step === 'payment') {
            $this->addComponent(PaymentMethodSelector::class, 'paymentMethodSelector', []);
        }
        $this->setData();
    }

    /**
     * The component is run.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Cms\Classes\CmsException
     */
    public function onRun()
    {
        // An off-site payment has been completed
        if ($type = request()->input('return')) {
            return $this->handleOffSiteReturn($type);
        }

        // If a invalid step is provided, show a 404 error page.
        if ( ! in_array($this->step, array_keys($this->getStepOptions()))) {
            return $this->controller->run('404');
        }

        // If an order has been created but something failed we can fetch the paymentError
        // from the order's payment logs.
        if ($this->step === 'failed' && $this->order) {
            $this->paymentError = optional($this->order->payment_logs->first())->message ?? 'Unknown error';
        }
    }

    /**
     * Where the magic happens.
     */
    public function onSubmit()
    {
        $data = post();

        // The user is not signed in. Let's create a new account.
        if ( ! $this->user) {
            $this->user = app(SignUpHandler::class)->handle($data, (bool)post('as_guest'));
            if ( ! $this->user) {
                throw new ValidationException(
                    [trans('offline.mall::lang.components.quickCheckout.errors.signup_failed')]
                );
            }
            $this->cart = $this->cart->refresh();
        }

        if ($this->cart->payment_method_id === null && $this->order === null) {
            throw new ValidationException(
                [trans('offline.mall::lang.components.checkout.errors.missing_settings')]
            );
        }

        $paymentData = post('payment_data', []);

        $model = $this->order ?? $this->cart;

        $paymentMethod = PaymentMethod::findOrFail($model->payment_method_id);

        // Grab the PaymentGateway from the Service Container.
        $gateway = app(PaymentGateway::class);
        $gateway->init($paymentMethod, $paymentData);

        // If an order is already available, this is not the normal checkout flow but a
        // subsequent try to pay for an existing order for which the payment failed.
        $flow = $this->order ? 'payment' : 'checkout';

        if ( ! $this->order) {
            // Create the order first.
            $this->order = DB::transaction(
                function () {
                    return Order::fromCart($this->cart);
                }
            );
        }

        // If the order was created successfully proceed with the payment.
        $paymentService = new PaymentService(
            $gateway,
            $this->order,
            $this->page->page->fileName
        );

        return $paymentService->process($flow);
    }

    /**
     * The shipping method has been changed.
     *
     * @return array
     * @throws ValidationException
     */
    public function onChangeShippingMethod()
    {
        $v = Validator::make(
            post(),
            [
                'id' => 'required|exists:offline_mall_shipping_methods,id',
            ]
        );

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $id = post('id');

        if ( ! $this->shippingMethods || ! $this->shippingMethods->contains($id)) {
            throw new ValidationException(
                [
                    'id' => trans('offline.mall::lang.components.shippingMethodSelector.errors.unavailable'),
                ]
            );
        }

        $method = ShippingMethod::find($id);
        $this->cart->setShippingMethod($method);
        $this->cart->validateShippingMethod();
        $this->setData();

        return $this->updateForm(
            [
                'method' => $method,
            ]
        );
    }

    /**
     * The payment method has been changed.
     *
     * @return array
     * @throws ValidationException
     */
    public function onChangePaymentMethod()
    {
        $rules = [
            'id' => 'required|exists:offline_mall_payment_methods,id',
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $id = post('id');

        $method = PaymentMethod::find($id);
        $this->cart->setPaymentMethod($method);
        $this->setData();

        return $this->updateForm(
            [
                'method' => $method,
            ]
        );
    }

    /**
     * Re-renders all dynamic form components. Additional
     * data to be returned to the partial can be specified.
     *
     * @param array $withData
     *
     * @return array
     */
    public function updateForm(array $withData = [])
    {
        $withAlias = function (string $partial) {
            return $this->alias . '::' . $partial;
        };

        return array_merge(
            [
                '.mall-quick-checkout__shipping-methods' => $this->renderPartial($withAlias('shippingmethod')),
                '.mall-quick-checkout__payment-methods' => $this->renderPartial($withAlias('paymentmethod')),
                '.mall-quick-checkout__cart' => $this->renderPartial($withAlias('cart')),
            ],
            $withData
        );
    }

    /**
     * Renders the payment form of the currently selected
     * payment method.
     *
     * @return string
     */
    public function renderPaymentForm()
    {
        if ( ! $this->cart->payment_method) {
            return '';
        }

        /** @var PaymentGateway $gateway */
        $gateway = app(PaymentGateway::class);

        return $gateway
            ->getProviderById($this->cart->payment_method->payment_provider)
            ->renderPaymentForm($this->cart);
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $this->loginPage = $this->property('loginPage');
        $this->currentPage = $this->page->page->getBaseFileName();
        $this->setVar('accountPage', GeneralSettings::get('account_page'));

        $this->setVar('user', Auth::getUser());
        $cart = Cart::byUser($this->user);
        if ( ! $cart->payment_method_id) {
            $cart->setPaymentMethod(PaymentMethod::getDefault());
        }
        $this->setVar('cart', $cart);

        $paymentMethod = PaymentMethod::find($cart->payment_method_id);
        if ( ! $paymentMethod) {
            $paymentMethod = PaymentMethod::getDefault();
            $cart->setPaymentMethod($paymentMethod);
        }
        $this->setVar('paymentMethods', PaymentMethod::orderBy('sort_order', 'ASC')->get());
        $this->setVar('customerPaymentMethods', $this->getCustomerMethods());


//        $this->setVar('dataLayer', $this->handleDataLayer());

        $this->countries = Country::getNameList();
        $this->useState = GeneralSettings::get('use_state', true);

        $this->setVar('shippingMethods', ShippingMethod::getAvailableByCart($cart));
        if ($orderId = request()->get('order')) {
            $orderId = $this->decode($orderId);
            $this->setVar('order', Order::byCustomer(optional($this->user)->customer)->find($orderId));
        }
    }

    /**
     * Return all CustomerPaymentMethods grouped
     * by the payment method.
     *
     * @return Collection
     */
    protected function getCustomerMethods()
    {
        if ( ! optional(Auth::getUser())->customer) {
            return collect([]);
        }

        return optional(Auth::getUser()->customer->payment_methods)->groupBy('payment_method_id');
    }

    /**
     * The user was redirected back to the store from an
     * external payment service.
     *
     * @param string $type
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Cms\Classes\CmsException
     */
    protected function handleOffSiteReturn($type)
    {
        return (new PaymentRedirector($this->page->page->fileName))->handleOffSiteReturn($type);
    }
}
