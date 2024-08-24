<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Wishlist;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing
 * system with the PriceBag class construct instead.
 */
class TotalsCalculatorInput
{
    /**
     * Associated Cart.
     * @var ?Cart
     */
    public $cart = null;

    /**
     * Associated Wishlist.
     * @var ?Wishlist
     */
    public $wishlist = null;

    /**
     * Collected products.
     * @var Collection<Product>
     */
    public $products;

    /**
     * Collected discounts.
     * @var Collection<Discount>
     */
    public $discounts;

    /**
     * Assigned shipping method.
     * @var ShippingMethod
     */
    public $shipping_method;

    /**
     * Shipping-related countryID.
     * @var int
     */
    public $shipping_country_id;

    /**
     * Assigned payment method.
     * @var PaymentMethod
     */
    public $payment_method;

    /**
     * Create a new TotalsCalculatorInput instance.
     * @param null|Cart|Wishlist $model
     */
    public function __construct($model)
    {
        if ($model instanceof Cart) {
            $this->cart = $model;
        } elseif ($model instanceof Wishlist) {
            $this->wishlist = $model;
        }
    }

    /**
     * Create an instance from a Cart model.
     * @param Cart $cart
     * @return TotalsCalculatorInput
     */
    public static function fromCart(Cart $cart)
    {
        $cart->loadMissing(
            'products',
            'products.data.taxes',
            'shipping_method',
            'shipping_method.taxes.countries',
            'shipping_method.rates',
            'discounts'
        );

        $input = new self($cart);
        $input->fill([
            'products'            => $cart->products,
            'shipping_method'     => $cart->shipping_method,
            'payment_method'      => $cart->payment_method,
            'discounts'           => $cart->discounts,
            'shipping_country_id' => optional(optional($cart)->shipping_address)->country_id ?? $cart->getFallbackShippingCountryId(),
        ]);

        return $input;
    }

    /**
     * Create an instance from a Wishlist model.
     * @param Wishlist $wishlist
     * @return TotalsCalculatorInput
     */
    public static function fromWishlist(Wishlist $wishlist)
    {
        $wishlist->loadMissing('items.data.taxes');

        $input = new self($wishlist);
        $input->fill([
            'products'            => $wishlist->items,
            'discounts'           => new Collection(),
            'shipping_method'     => $wishlist->shipping_method,
            'shipping_country_id' => $wishlist->getCartCountryId(),
        ]);

        return $input;
    }

    /**
     * Fill class properties.
     * @param array $params
     * @return void
     */
    public function fill(array $params)
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
