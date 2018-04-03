<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Contracts\Encryption\DecryptException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentRedirector;
use OFFLINE\Mall\Classes\Payments\PaymentService;
use OFFLINE\Mall\Components\Cart as CartComponent;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Redirect;
use Request;
use Session;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Checkout extends MallComponent
{
    public $cart;
    public $paymentError;
    public $step;
    public $order;
    public $accountPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.checkout.details.name',
            'description' => 'offline.mall::lang.components.checkout.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'step' => [
                'type' => 'dropdown',
                'name' => 'offline.mall::lang.components.checkout.properties.step.name',
            ],
        ];
    }

    public function getStepOptions()
    {
        return [
            'payment'   => trans('offline.mall::lang.components.checkout.steps.payment'),
            'shipping'  => trans('offline.mall::lang.components.checkout.steps.shipping'),
            'confirm'   => trans('offline.mall::lang.components.checkout.steps.confirm'),
            'failed'    => trans('offline.mall::lang.components.checkout.steps.failed'),
            'cancelled' => trans('offline.mall::lang.components.checkout.steps.cancelled'),
            'done'      => trans('offline.mall::lang.components.checkout.steps.done'),
        ];
    }

    public function init()
    {
        $this->addComponent(CartComponent::class, 'cart', ['showDiscountApplier' => false]);
        $this->addComponent(AddressSelector::class, 'billingAddressSelector', ['type' => 'billing']);
        $this->addComponent(AddressSelector::class, 'shippingAddressSelector', ['type' => 'shipping']);
        $this->addComponent(ShippingSelector::class, 'shippingSelector', []);
        $this->addComponent(PaymentMethodSelector::class, 'paymentMethodSelector', []);
        $this->setData();
    }

    public function onRun()
    {
        // An off-site payment has been completed
        if ($type = request()->input('return')) {
            return $this->handleOffSiteReturn($type);
        }

        // If no step is provided or the step is invalid redirect the user to
        // the payment method selection screen.
        $step = $this->property('step');
        if ( ! $step || ! array_key_exists($step, $this->getStepOptions())) {
            $url = $this->stepUrl('payment');

            return redirect()->to($url);
        }

        if ($step === 'failed' && $this->order) {
            $this->paymentError = optional($this->order->payment_logs->first())->message ?? 'Unknown error';
        }
    }

    public function onCheckout()
    {
        $this->setData();

        if ($this->cart->shipping_method_id === null || $this->cart->payment_method_id === null) {
            throw new ValidationException([trans('offline.mall::lang.components.checkout.errors.missing_settings')]);
        }

        try {
            $paymentData = json_decode(decrypt(session()->get('mall.payment_method.data')), true);
        } catch (DecryptException $e) {
            $paymentData = [];
        }

        $paymentMethod = PaymentMethod::findOrFail($this->cart->payment_method_id);

        $gateway = app(PaymentGateway::class);
        $gateway->init($paymentMethod, $paymentData);

        $order = DB::transaction(function () {
            return Order::fromCart($this->cart);
        });

        $paymentService = new PaymentService(
            $gateway,
            $order,
            $this->page->page->fileName
        );

        return $paymentService->process();
    }

    protected function setData()
    {
        $user = Auth::getUser();
        $cart = Cart::byUser($user);
        if ( ! $cart->payment_method_id) {
            $cart->setPaymentMethod(PaymentMethod::getDefault());
        }
        $this->setVar('cart', $cart);
        $this->setVar('paymentMethod', PaymentMethod::findOrFail($cart->payment_method_id));
        $this->setVar('step', $this->property('step'));
        $this->setVar('accountPage', GeneralSettings::get('account_page'));

        if ($orderId = request()->get('order')) {
            $orderId = $this->decode($orderId);
            $this->setVar('order', Order::byCustomer($user->customer)->find($orderId));
        }
    }

    protected function handleOffSiteReturn($type)
    {
        return (new PaymentRedirector($this->page->page->fileName))->handleOffSiteReturn($type);
    }

    public function stepUrl($step, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['step' => $step])
        );
    }
}
