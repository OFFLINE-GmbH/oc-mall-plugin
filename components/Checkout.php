<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use DB;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Components\Cart as CartComponent;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Redirect;
use Request;
use Session;

class Checkout extends ComponentBase
{
    use SetVars;

    public $cart;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.checkout.details.name',
            'description' => 'offline.mall::lang.components.checkout.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $this->addComponent(CartComponent::class, 'cart', []);
        $this->addComponent(AddressSelector::class, 'addressSelector', []);
        $this->addComponent(ShippingSelector::class, 'shippingSelector', []);
        $this->addComponent(PaymentMethodSelector::class, 'paymentMethodSelector', []);
        $this->setData();
    }

    public function onRun()
    {
        // An off-site payment has been completed
        if ($type = Request::input('return')) {
            return $this->handleOffSiteReturn($type);
        }
    }

    protected function setData()
    {
        $cart = Cart::byUser(Auth::getUser());
        $cart->setPaymentMethod(PaymentMethod::find(2));

        $this->setVar('cart', $cart);
    }

    public function onCheckout()
    {
        $this->setData();
        $order = DB::transaction(function () {
            $order = Order::fromCart($this->cart);
            $order->save();

            return $order;
        });

        $data = [
            'number'      => '4242424242424242',
            'expiryMonth' => 6,
            'expiryYear'  => 2019,
            'cvv'         => '123',
        ];

        $gateway = app(PaymentGateway::class);
        $result  = $gateway->process($order, $data);

        return $this->handlePaymentResult($result);
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    protected function handlePaymentResult($result)
    {
        if ($result->redirect) {
            return $result->redirectUrl ? Redirect::to($result->redirectUrl) : $result->redirectResponse;
        }

        if ($result->successful) {
            return Redirect::to($this->getSuccessfulUrl());
        }

        return Redirect::to($this->getFailedUrl());
    }

    protected function handleOffSiteReturn($type)
    {
        // Someone tampered with the url or the session has expired.
        $paymentId = Session::pull('oc-mall.payment.id');
        if ($paymentId !== Request::input('oc-mall_payment_id')) {
            Session::forget('oc-mall.payment.callback');

            return Redirect::to($this->getFailedUrl());
        }

        // The user has cancelled the payment
        if ($type === 'cancel') {
            Session::forget('oc-mall.payment.callback');

            return Redirect::to($this->getCancelledUrl());
        }

        // If a callback is set we need to do an additional step to
        // complete this payment.
        $callback = Session::pull('oc-mall.payment.callback');
        if ($callback) {
            $paymentMethod = new $callback;

            if ( ! method_exists($paymentMethod, 'complete')) {
                throw new \LogicException('Payment gateways that redirect off-site need to have a "complete" method!');
            }

            return $this->handlePaymentResult($paymentMethod->complete());
        }

        // The payment was successful
        return Redirect::to($this->getSuccessfulUrl());
    }

    private function getFailedUrl()
    {
        return '/failed';
    }

    private function getCancelledUrl()
    {
        return '/cancelled';
    }

    private function getSuccessfulUrl()
    {
        return '/done';
    }
}
