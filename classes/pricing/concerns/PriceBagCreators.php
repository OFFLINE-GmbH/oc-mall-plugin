<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use October\Rain\Database\Collection;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Wishlist;

trait PriceBagCreators
{
    /**
     * Create PriceBag from cart.
     * @param Cart $cart
     * @return self
     */
    public static function fromCart(Cart $cart): self
    {
        $cart->loadMissing([
            'products',
            'products.data.taxes',
            'shipping_method',
            'shipping_method.taxes.countries',
            'shipping_method.rates',
            'discounts',
        ]);

        $currency = Currency::activeCurrency();
        $bag = new static();

        // Add Products and Services
        foreach ($cart->products as $product) {
            $element = $product->variant ?? $product->product;

            // Get prices from CartProduct (incl. custom field prices)
            $prices = array_map(fn ($price) => intval($price * 100), $product->price);

            // Add Record
            $record = $bag->addProduct(
                $element,
                $prices[$currency->code],
                $product->quantity,
                $element->price_includes_tax == true
            );

            // Set Weight
            if (!empty($element->weight)) {
                $record->setWeight($element->weight);
            }

            // Set Taxes
            $taxes = $product->filtered_product_taxes;

            if ($taxes->count() == 1) {
                $record->setVat($taxes->first()->percentage);
            } elseif ($taxes->count() > 1) {
                $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
            }

            // Set Service Options
            foreach ($product->service_options as $option) {
                $service = $option->service()->first();
                $prices = $option->prices()->get()->mapWithKeys(fn ($price) => [$price->currency->code => $price->integer]);
                $record = $bag->addService(
                    $option,
                    $prices[$currency->code],
                    $product->quantity ?? 1,
                    $element->price_includes_tax == true
                );

                $taxes = $bag->getFilteredTaxes($service->taxes);

                if ($taxes->count() == 1) {
                    $record->setVat($taxes->first()->percentage);
                } elseif ($taxes->count() > 1) {
                    $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
                }
            }
        }

        // Add Shipping
        if ($cart->shipping_method) {
            self::bagAddShippingMethod($bag, $currency, $cart->shipping_method, $cart);
        }
        
        // Add Payment
        if ($cart->payment_method) {
            self::bagAddPaymentMethod($bag, $currency, $cart->payment_method, $cart);
        }

        // Add Discounts
        if ($cart->discounts) {
            self::bagAddDiscounts($bag, $currency, $cart->discounts, $cart);
        }

        return $bag;
    }

    /**
     * Create PriceBag from existing order.
     * @param Order $order
     * @return self
     */
    public static function fromOrder(Order $order): self
    {
        $bag = new static();

        return $bag;
    }

    /**
     * Create PriceBag from wishlist.
     * @param Wishlist $wishlist
     * @return self
     */
    public static function fromWishlist(Wishlist $wishlist): self
    {
        $bag = new static();

        return $bag;
    }

    /**
     * Create PriceBag from legacy TotalsCalculatorInput.
     * @param TotalsCalculatorInput $input
     * @return self
     */
    public static function fromTotalsCalculatorInput(TotalsCalculatorInput $input): self
    {
        if (!empty($input->cart)) {
            return self::fromCart($input->cart);
        } elseif (!empty($input->wishlist)) {
            return self::fromWishlist($input->wishlist);
        } else {
            throw new PriceBagException('The passed TotalsCalculatorInput is invalid or corrupt.');
        }
    }

    /**
     * Add shipping method to bag.
     * @internal Used by the internal bag creator methods above only.
     * @param PriceBag $bag
     * @param Currency $currency
     * @param ShippingMethod $method
     * @return void
     */
    protected static function bagAddShippingMethod(PriceBag $bag, Currency $currency, ShippingMethod $method, Cart $cart)
    {
        $prices = array_map(fn ($val) => $val->integer, $method->actual_prices);

        // Add Record
        $record = $bag->addShippingMethod(
            $method,
            $prices[$currency->code],
            $method->price_includes_tax
        );

        foreach ($method->rates as $rate) {
            $prices = $rate->prices()->get()->mapWithKeys(fn ($price) => [$price->currency->code => $price->integer]);
            $record->addRate($rate->from_weight, $rate->to_weight, $prices[$currency->code]);
        }

        // Add Taxes
        $taxes = $bag->getFilteredTaxes($method->taxes);

        if ($taxes->count() == 1) {
            $record->setVat($taxes->first()->percentage);
        } elseif ($taxes->count() > 1) {
            $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
        }
    }

    /**
     * Add payment method to bag.
     * @internal Used by the internal bag creator methods above only.
     * @param PriceBag $bag
     * @param Currency $currency
     * @param PaymentMethod $method
     * @return void
     */
    protected static function bagAddPaymentMethod(PriceBag $bag, Currency $currency, PaymentMethod $method, Cart $cart)
    {
        $prices = $method->prices()->get()->mapWithKeys(fn ($price) => [$price->currency->code => $price->integer]);

        // Add Record
        $record = $bag->addPaymentMethod(
            $method,
            $method->fee_percentage ?? 0,
            $prices[$currency->code] ?? 0
        );

        // Add Taxes
        $taxes = $bag->getFilteredTaxes($method->taxes);

        if ($taxes->count() > 0) {
            $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
        }
    }

    /**
     * Add discounts to bag.
     * @internal Used by the internal bag creator methods above only.
     * @param PriceBag $bag
     * @param Currency $currency
     * @param array|Collection $discounts
     * @return void
     */
    protected static function bagAddDiscounts(PriceBag $bag, Currency $currency, $discounts, Cart $cart)
    {
        foreach ($discounts as $discount) {
            if ($discount->type == 'fixed_amount') {
                $bag->addDiscount($discount, $discount->amount()->integer);
            }

            if ($discount->type == 'rate') {
                $bag->addDiscount($discount, $discount->rate, true);
            }

            if ($discount->type == 'shipping') {
                $prices = $discount->shipping_prices()->get()->mapWithKeys(fn ($price) => [$price->currency->code => $price->integer]);
                $bag->addDiscount($discount, $prices[$currency->code], false);
            }
        }
    }
}
