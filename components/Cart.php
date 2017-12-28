<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\Stripe;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;

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

        if ($result->successful) {
            return Redirect::to('/done');
        } else {
            return Redirect::to('/failed');
        }
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
}
