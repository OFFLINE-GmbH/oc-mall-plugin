<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use Event;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\Location\Models\Country;

class PaymentMethodTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set Country
        Event::listen('mall.cart.setCountry', function ($model) {
            $model->countryId = 14;
        });
    }

    /**
     * Test if only enabled payment methods are returned.
     * @return void
     */
    public function test_return_only_enabled_payment_methods()
    {
        $invoiceMethod = PaymentMethod::where('code', 'invoice')->first();
        $invoiceMethod->is_enabled = false;
        $invoiceMethod->save();

        $methods = PaymentMethod::get()->map(fn ($method) => $method->code)->toArray();
        $this->assertEquals($methods, ['paypal', 'stripe']);
    }

    /**
     * Test if fees are added to order total value.
     * @return void
     */
    public function test_fees_are_added_to_order_total()
    {
        $product = $this->getProduct(100);
        $currency = Currency::where('code', 'CHF')->first();

        // Create Payment Method
        $method = PaymentMethod::create([
            'name'              => 'Test',
            'fee_percentage'    => 2.9,
            'payment_provider'  => 'stripe',
        ]);
        $method->prices()->save(new Price([
            'currency_id' => $currency->id,
            'price'       => 0.30,
        ]));

        // Create and assign Cart
        $cart = new Cart();
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($method);

        // Test Pricing
        $this->assertEquals(10320, round($cart->totals()->totalPostTaxes(), 0));
        $this->assertEquals(320, round($cart->totals()->paymentTotal()->totalPreTaxes(), 0));
        $this->assertEquals(0, round($cart->totals()->paymentTotal()->totalTaxes(), 0));
        $this->assertEquals(320, round($cart->totals()->paymentTotal()->totalPostTaxes(), 0));
    }

    /**
     * Test if fees are added to order total value with taxes.
     * @return void
     */
    public function test_fees_are_added_to_order_total_with_taxes()
    {
        $product = $this->getProduct(100);
        $currency = Currency::where('code', 'CHF')->first();

        // Create Payment Method
        $method = PaymentMethod::create([
            'name'              => 'Test',
            'fee_percentage'    => 2.9,
            'payment_provider'  => 'stripe',
        ]);
        $method->prices()->save(new Price([
            'currency_id'   => $currency->id,
            'price'         => 0.30,
        ]));
        $method->taxes()->save(new Tax([
            'name'          => 'Payment Tax 1',
            'percentage'    => 5,
        ]));
        $method->taxes()->save(new Tax([
            'name'          => 'Payment Tax 2',
            'percentage'    => 5,
        ]));

        // Create and assign Cart
        $cart = new Cart();
        $cart->shipping_address_id = Address::first()->id;
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($method);

        // Test Pricing
        $this->assertEquals(10352, round($cart->totals()->totalPostTaxes()));
        $this->assertEquals(320, round($cart->totals()->paymentTotal()->totalPreTaxes()));
        $this->assertEquals(32, round($cart->totals()->paymentTotal()->totalTaxes()));
        $this->assertEquals(352, round($cart->totals()->paymentTotal()->totalPostTaxes()));

        // Test applied taxes
        $this->assertEquals(1, $cart->totals()->taxes()->count());
        $this->assertEquals(32, round($cart->totals()->taxes()->last()->total()));
    }

    /**
     * Test if country-specific taxes are added.
     * @return void
     */
    public function test_country_specific_taxes_are_added()
    {
        $product = $this->getProduct(100);
        $currency = Currency::where('code', 'CHF')->first();

        // Create Address
        $address = Address::create([
            'name'        => 'Mr. Miller',
            'lines'       => 'Street 12',
            'zip'         => '6003',
            'city'        => 'Lucerne',
            'customer_id' => 1,
            'country_id'  => Country::where('code', 'CH')->first()->id,
        ]);

        // Create Country Tax
        $countryTax = Tax::create([
            'name'       => 'Payment Tax 1',
            'percentage' => 5,
        ]);
        $countryTax->countries()->attach(1);

        // Create Payment Method
        $method = PaymentMethod::create([
            'name'             => 'Test',
            'fee_percentage'   => 2.9,
            'payment_provider' => 'stripe',
        ]);
        $method->prices()->save(new Price([
            'currency_id'   => $currency->id,
            'price'         => 0.30,
        ]));
        $method->taxes()->save($countryTax);
        $method->taxes()->save(new Tax([
            'name'       => 'Payment Tax 2',
            'percentage' => 10,
        ]));

        // Create and assign Cart
        $cart = new Cart();
        $cart->addProduct($product, 1);
        $cart->setShippingAddress($address);
        $cart->setPaymentMethod($method);

        // Test Pricing
        $this->assertEquals(10352, round($cart->totals()->totalPostTaxes()));
        $this->assertEquals(320, round($cart->totals()->paymentTotal()->totalPreTaxes()));
        $this->assertEquals(32, round($cart->totals()->paymentTotal()->totalTaxes()));
        $this->assertEquals(352, round($cart->totals()->paymentTotal()->totalPostTaxes()));

        // Test applied taxes
        $this->assertEquals(1, $cart->totals()->taxes()->count());
    }

    /**
     * Get product with and adjusted price.
     * @return mixed
     */
    protected function getProduct(int $price): Product
    {
        $product = Product::first();
        $product->save();
        $product->price = ['CHF' => $price, 'EUR' => 150];

        return Product::first();
    }
}
