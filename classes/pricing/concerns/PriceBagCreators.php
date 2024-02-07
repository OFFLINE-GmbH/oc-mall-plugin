<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use October\Rain\Database\Collection;
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
    static public function fromCart(Cart $cart): self
    {
        $cart->loadMissing([
            'products',
            'products.data.taxes',
            'shipping_method',
            'shipping_method.taxes.countries',
            'shipping_method.rates',
            'discounts'
        ]);

        $currency = Currency::activeCurrency();
        $bag = new static();

        // Add Products and Services
        foreach ($cart->products as $product) {
            $record = $bag->addProduct(
                $product, 
                $product->price[$currency->code],
                $product->quantity,
                $product->product->price_includes_tax == true
            );

            $taxes = $product->filtered_product_taxes;
            if ($taxes->count() == 1) {
                $record->setVat($taxes->first()->percentage);
            } else if ($taxes->count() > 1) {
                $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
            }

            foreach ($product->service_options AS $option) {
                $service = $option->service()->first();
                $prices = $option->prices()->get()->mapWithKeys(function ($price) {
                    return [$price->currency->code => $price->integer];
                });
                $record = $bag->addService(
                    $option, 
                    $prices[$currency->code],
                    $product->quantity ?? 1,
                    $product->product->price_includes_tax == true
                );

                $taxes = $service->taxes;
                if ($taxes->count() == 1) {
                    $record->setVat($taxes->first()->percentage);
                } else if ($taxes->count() > 1) {
                    $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
                }
            }
        }

        // Add Shipping
        if ($cart->shipping_method) {
            self::bagAddShippingMethod($bag, $currency, $cart->shipping_method);
        }
        
        // Add Payment
        if ($cart->payment_method) {
            self::bagAddPaymentMethod($bag, $currency, $cart->payment_method);
        }

        // Add Discounts
        if ($cart->discounts) {
            self::bagAddDiscounts($bag, $currency, $cart->discounts);
        }

        return $bag;
    }

    /**
     * Create PriceBag from existing order.
     * @param Order $order
     * @return self
     */
    static public function fromOrder(Order $order): self
    {

        $bag = new static();

        return $bag;
    }

    /**
     * Create PriceBag from wishlist.
     * @param Wishlist $wishlist
     * @return self
     */
    static public function fromWishlist(Wishlist $wishlist): self
    {

        $bag = new static();

        return $bag;
    }

    /**
     * Create PriceBag from legacy TotalsCalculatorInput.
     * @param TotalsCalculatorInput $input
     * @return self
     */
    static public function fromTotalsCalculatorInput(TotalsCalculatorInput $input): self
    {
        $currency = Currency::activeCurrency();
        $bag = new static();

        foreach ($input->products as $product) {
            $record = $bag->addProduct(
                $product, 
                $product->price[$currency->code],
                $product->quantity
            );

            $taxes = $product->filtered_product_taxes;
            if ($taxes->count() == 1) {
                $record->setVat($taxes->first()->percentage);
            } else if ($taxes->count() > 1) {
                $taxes->each(fn ($tax) => $record->addTax($tax->percentage));
            }
        }

        return $bag;
    }

    /**
     * Add shipping method to bag.
     * @internal Used by the internal bag creator methods above only.
     * @param PriceBag $bag
     * @param Currency $currency
     * @param ShippingMethod $method
     * @return void
     */
    static protected function bagAddShippingMethod(PriceBag $bag, Currency $currency, ShippingMethod $method)
    {
        $prices = $method->prices()->get()->mapWithKeys(function ($price) {
            return [$price->currency->code => $price->integer];
        });

        // Add Record
        $record = $bag->addShippingMethod(
            $method, 
            $prices[$currency->code],
            $method->price_includes_tax
        );

        // Add Taxes
        $taxes = $method->taxes;
        if ($taxes->count() == 1) {
            $record->setVat($taxes->first()->percentage);
        } else if ($taxes->count() > 1) {
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
    static protected function bagAddPaymentMethod(PriceBag $bag, Currency $currency, PaymentMethod $method)
    {
        $prices = $method->prices()->get()->mapWithKeys(function ($price) {
            return [$price->currency->code => $price->integer];
        });
        $fee = $method->payment_fee;

    }

    /**
     * Add discounts to bag.
     * @internal Used by the internal bag creator methods above only.
     * @param PriceBag $bag
     * @param Currency $currency
     * @param array|Collection $discounts
     * @return void
     */
    static protected function bagAddDiscounts(PriceBag $bag, Currency $currency, array|Collection $discounts)
    {
        foreach ($discounts AS $discount) {
            if ($discount->type == 'fixed_amount') {
                $bag->addDiscount('products', $discount->amount()->integer);
            }
            if ($discount->type == 'rate') {
                $bag->addDiscount('products', $discount->rate, true);
            }
        }
    }
}
