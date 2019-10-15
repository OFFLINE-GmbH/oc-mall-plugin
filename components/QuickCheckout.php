<?php namespace OFFLINE\Mall\Components;

use Auth;
use Faker\Provider\Payment;
use Illuminate\Support\Collection;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\DefaultPaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentProvider;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
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
        return [];
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
        $cart = Cart::byUser(Auth::getUser());
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
