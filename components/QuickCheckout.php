<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Support\Collection;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
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
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.quickCheckout.details.name',
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
                'default' => 'login'
            ]
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
        $this->addComponent(AddressSelector::class, 'billingAddressSelector', ['type' => 'billing']);
        $this->addComponent(AddressSelector::class, 'shippingAddressSelector', ['type' => 'shipping']);
    }

    /**
     * The component is run.
     *
     * @return void
     */
    public function onRun()
    {
        $this->setData();
    }

    /**
     * Where the magic happens.
     */
    public function onSubmit()
    {
        $this->setData();

        $data = post();

        // The user is not signed in. Let's create a new account.
        if (!$this->user) {
            $this->user = app(SignUpHandler::class)->handle($data, (bool)post('as_guest'));
            if (!$this->user) {
                throw new ValidationException(
                    [trans('offline.mall::lang.components.quickCheckout.errors.signup_failed')]
                );
            }
        }

        if ($this->cart->payment_method_id === null) {
            throw new ValidationException(
                [trans('offline.mall::lang.components.checkout.errors.missing_settings')]
            );
        }

        $paymentData = post('payment_data', []);

        $paymentMethod = PaymentMethod::findOrFail($this->cart->payment_method_id);

        // Grab the PaymentGateway from the Service Container.
        $gateway = app(PaymentGateway::class);
        $gateway->init($paymentMethod, $paymentData);

        // Create the order first.
        $order = DB::transaction(function () {
            return Order::fromCart($this->cart);
        });

        // If the order was created successfully proceed with the payment.
        $paymentService = new PaymentService(
            $gateway,
            $order,
            $this->page->page->fileName
        );

        return $paymentService->process();
    }

    /**
     * The shipping method has been changed.
     *
     * @return array
     * @throws ValidationException
     */
    public function onChangeShippingMethod()
    {
        $this->setData();

        $v = Validator::make(post(), [
            'id' => 'required|exists:offline_mall_shipping_methods,id',
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $id = post('id');

        if ( ! $this->shippingMethods || ! $this->shippingMethods->contains($id)) {
            throw new ValidationException([
                'id' => trans('offline.mall::lang.components.shippingMethodSelector.errors.unavailable'),
            ]);
        }

        $method = ShippingMethod::find($id);
        $this->cart->setShippingMethod($method);
        $this->setData();

        return $this->updateForm([
            'method' => $method,
        ]);
    }

    /**
     * The payment method has been changed.
     *
     * @return array
     * @throws ValidationException
     */
    public function onChangePaymentMethod()
    {
        $this->setData();

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

        return $this->updateForm([
            'method' => $method,
        ]);
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

        return array_merge([
            '.mall-quick-checkout__shipping-methods' => $this->renderPartial($withAlias('shippingmethod')),
            '.mall-quick-checkout__payment-methods' => $this->renderPartial($withAlias('paymentmethod')),
            '.mall-quick-checkout__cart' => $this->renderPartial($withAlias('cart')),
        ], $withData);
    }

    /**
     * Renders the payment form of the currently selected
     * payment method.
     *
     * @return string
     */
    public function renderPaymentForm()
    {
        if (!$this->cart->payment_method) {
            return '';
        }

        /** @var PaymentGateway $gateway */
        $gateway  = app(PaymentGateway::class);
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
        $this->setVar('accountPage', GeneralSettings::get('account_page'));
        $this->currentPage = $this->page->page->getBaseFileName();

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
        $this->useState  = GeneralSettings::get('use_state', true);

        $this->setVar('shippingMethods', ShippingMethod::getAvailableByCart($cart));
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
}
