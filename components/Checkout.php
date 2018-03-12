<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use DB;
use Illuminate\Contracts\Encryption\DecryptException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentResult;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Components\Cart as CartComponent;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Redirect;
use Request;
use Session;

class Checkout extends ComponentBase
{
    use SetVars;
    use HashIds;

    public $cart;
    public $payment_method;
    public $step;
    public $order;
    public $customerProfilePage;

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

        $gateway = app(PaymentGateway::class);
        $gateway->init($this->cart, $paymentData);

        $order = DB::transaction(function () {
            return Order::fromCart($this->cart);
        });

        session()->put('mall.processing_order.id', $order->hashId);

        try {
            $result = $gateway->process($order);
        } catch (\Throwable $e) {
            $result             = new PaymentResult();
            $result->successful = false;
            $result->message    = $e->getMessage();
        }

        session()->forget('mall.payment_method.data');

        return $this->handlePaymentResult($result);
    }

    protected function setData()
    {
        $user = Auth::getUser();
        $cart = Cart::byUser($user);
        if ( ! $cart->payment_method_id) {
            $cart->setPaymentMethod(PaymentMethod::getDefault());
        }
        $this->setVar('cart', $cart);
        $this->setVar('payment_method', PaymentMethod::findOrFail($cart->payment_method_id));
        $this->setVar('step', $this->property('step'));
        $this->setVar('customerProfilePage', GeneralSettings::get('customer_profile_page'));

        if ($orderId = request()->get('order')) {
            $orderId = $this->decode($orderId);
            $this->setVar('order', Order::byCustomer($user->customer)->find($orderId));
        }
    }

    protected function handlePaymentResult($result)
    {
        if ($result->redirect) {
            return $result->redirectUrl ? Redirect::to($result->redirectUrl) : $result->redirectResponse;
        }

        if ($result->successful) {
            return $this->finalRedirect('successful');
        }

        return $this->finalRedirect('failed');
    }

    protected function handleOffSiteReturn($type)
    {
        // Someone tampered with the url or the session has expired.
        $paymentId = session()->pull('oc-mall.payment.id');
        if ($paymentId !== request()->input('oc-mall_payment_id')) {
            session()->forget('oc-mall.payment.callback');

            return $this->finalRedirect('failed');
        }

        // The user has cancelled the payment
        if ($type === 'cancel') {
            session()->forget('oc-mall.payment.callback');

            return $this->finalRedirect('cancelled');
        }

        // If a callback is set we need to do an additional step to
        // complete this payment.
        $callback = session()->pull('oc-mall.payment.callback');
        if ($callback) {
            $paymentMethod = new $callback;

            if ( ! method_exists($paymentMethod, 'complete')) {
                throw new \LogicException('Payment gateways that redirect off-site need to have a "complete" method!');
            }

            return $this->handlePaymentResult($paymentMethod->complete());
        }

        // The payment was successful
        return $this->finalRedirect('successful');
    }

    public function stepUrl($step, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['step' => $step])
        );
    }

    protected function finalRedirect($state)
    {
        $states = [
            'failed'     => $this->getFailedUrl(),
            'cancelled'  => $this->getCancelledUrl(),
            'successful' => $this->getSuccessfulUrl(),
        ];

        $orderId = session()->pull('mall.processing_order.id');

        $url = $states[$state];
        if ($orderId) {
            $url .= '?order=' . $orderId;
        }

        return redirect()->to($url);
    }

    protected function getFailedUrl()
    {
        return $this->stepUrl('failed');
    }

    protected function getCancelledUrl()
    {
        return $this->stepUrl('cancelled');
    }

    protected function getSuccessfulUrl()
    {
        return $this->stepUrl('done');
    }
}
