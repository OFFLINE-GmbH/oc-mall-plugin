<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PayPalRest;
use OFFLINE\Mall\Classes\Payments\Stripe;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;
use Request;
use Session;

class Cart extends ComponentBase
{
    use SetVars;

    public $cart;
    public $defaultMinQuantity = 1;
    public $defaultMaxQuantity = 100;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.cart.details.name',
            'description' => 'offline.mall::lang.components.cart.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        // An off-site payment has been completed
        if ($type = Request::input('return')) {
            return $this->handleOffSiteReturn($type);
        }

        $this->addJs('assets/pubsub.js');
        $this->setData();
    }

    public function onCheckout()
    {
        $this->setData();

        $result = \DB::transaction(function () {
            $order = Order::fromCart($this->cart);
            $order->save();

            $data = [
                'number'      => '4242424242424242',
                'expiryMonth' => 6,
                'expiryYear'  => 2019,
                'cvv'         => '123',
            ];

            $gateway = app(PaymentGateway::class);

            return $gateway->process($order, $data);
        });

        return $this->handlePaymentResult($result);
    }

    public function onUpdateQuantity()
    {
        // Make sure the product is actually in the logged
        // in user's shopping cart.
        $cart    = CartModel::byUser(Auth::getUser());
        $product = CartProduct
            ::whereHas('cart', function ($query) use ($cart) {
                $query->where('id', $cart->id);
            })
            ->where('id', (int)input('id'))
            ->firstOrFail();

        $cart->setQuantity($product->id, (int)input('quantity'));

        $this->setData();
    }

    protected function setData()
    {
        $cart = CartModel::byUser(Auth::getUser());
        $cart->addProduct(Product::first());
        $cart->setPaymentMethod(new Stripe());

        $this->setVar('cart', $cart);
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
