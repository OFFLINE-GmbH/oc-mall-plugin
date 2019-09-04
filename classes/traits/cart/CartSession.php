<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;

use Cookie;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Customer;
use RainLab\User\Models\User;
use Session;

trait CartSession
{
    public static function byUser(?User $user)
    {
        if ($user === null || $user->customer === null) {
            return self::bySession();
        }

        $cart = self::orderBy('created_at', 'DESC')
                    ->firstOrCreate(['customer_id' => $user->customer->id]);

        if ( ! $cart->shipping_address_id || ! $cart->billing_address_id) {
            if ( ! $cart->shipping_address_id) {
                $cart->shipping_address_id = $user->customer->default_shipping_address_id;
            }
            if ( ! $cart->billing_address_id) {
                $cart->billing_address_id = $user->customer->default_billing_address_id;
            }
            $cart->save();
        }

        return $cart;
    }

    /**
     * Create a cart for an unregistered user. The cart id
     * is stored to the session and to a cookie. When the user
     * visits the website again we will try to fetch the id of an old
     * cart from the session or from the cookie.
     *
     * @return Cart
     */
    protected static function bySession(): Cart
    {
        $sessionId = Session::get('cart_session_id') ?? Cookie::get('cart_session_id') ?? str_random(100);
        Cookie::queue('cart_session_id', $sessionId, 9e6);
        Session::put('cart_session_id', $sessionId);

        return self::orderBy('created_at', 'DESC')->firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Transfer a session attached cart to a customer.
     *
     * @param $customer
     *
     * @return Cart
     */
    public static function transferSessionCartToCustomer(Customer $customer): Cart
    {
        $cart = self::bySession();

        return $cart->transferToCustomer($customer);
    }

    /**
     * Transfer a cart to a customer.
     *
     * @param $customer
     *
     * @return Cart
     */
    public function transferToCustomer(Customer $customer): Cart
    {
        $shippingId = $customer->default_shipping_address_id ?? $customer->default_billing_address_id;

        // If there is an old Cart from this customer, merge the contents of the current
        // cart with the old contents.
        $existing = Cart::where('customer_id', $customer->id)->whereNull('session_id')->first();
        if ($existing) {
            CartProduct::where('cart_id', $existing->id)->update(['cart_id' => $this->id]);
            $existing->delete();
        }

        $this->session_id          = null;
        $this->customer_id         = $customer->id;
        $this->billing_address_id  = $customer->default_billing_address_id;
        $this->shipping_address_id = $shippingId;

        $this->save();

        return $this;
    }
}
