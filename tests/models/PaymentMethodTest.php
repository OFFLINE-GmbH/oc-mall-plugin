<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\Location\Models\Country;

class PaymentMethodTest extends PluginTestCase
{
    public function test_fees_are_added_to_order_total()
    {
        $product = $this->getProduct(100);
        $cart    = new Cart();

        $cart->addProduct($product, 1);

        $method                   = new PaymentMethod();
        $method->name             = 'Test';
        $method->fee_percentage   = 2.9;
        $method->payment_provider = 'stripe';
        $method->save();
        $method->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 0.30,
        ]));

        $cart->setPaymentMethod($method);

        $this->assertEquals(10330, round($cart->totals()->totalPostTaxes(), 0));
        $this->assertEquals(330, round($cart->totals()->paymentTotal()->totalPreTaxes(), 0));
        $this->assertEquals(0, round($cart->totals()->paymentTotal()->totalTaxes(), 0));
        $this->assertEquals(330, round($cart->totals()->paymentTotal()->totalPostTaxes(), 0));
    }

    public function test_fees_are_added_to_order_total_with_taxes()
    {
        $product = $this->getProduct(100);
        $cart    = new Cart();
        $cart->shipping_address_id = Address::first()->id;

        $cart->addProduct($product, 1);

        $method                   = new PaymentMethod();
        $method->name             = 'Test';
        $method->fee_percentage   = 2.9;
        $method->payment_provider = 'stripe';
        $method->save();
        $method->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 0.30,
        ]));
        $method->taxes()->save(new Tax([
            'name'       => 'Payment Tax 1',
            'percentage' => 5,
        ]));
        $method->taxes()->save(new Tax([
            'name'       => 'Payment Tax 2',
            'percentage' => 5,
        ]));

        $cart->setPaymentMethod($method);

        $this->assertEquals(10364, round($cart->totals()->totalPostTaxes()));
        $this->assertEquals(330, round($cart->totals()->paymentTotal()->totalPreTaxes()));
        $this->assertEquals(34, round($cart->totals()->paymentTotal()->totalTaxes()));
        $this->assertEquals(364, round($cart->totals()->paymentTotal()->totalPostTaxes()));

        $this->assertEquals(2, $cart->totals()->taxes()->count());
        $this->assertEquals(16, round($cart->totals()->taxes()->last()->total()));
    }

    public function test_country_specific_taxes_are_added()
    {
        $product = $this->getProduct(100);
        $cart    = new Cart();

        $cart->addProduct($product, 1);

        $address              = new Address();
        $address->name        = 'Mr. Miller';
        $address->lines       = 'Street 12';
        $address->zip         = '6003';
        $address->city        = 'Lucerne';
        $address->customer_id = 1;
        $address->country_id  = Country::where('code', 'CH')->first()->id;
        $address->save();

        $cart->setShippingAddress($address);

        $method                   = new PaymentMethod();
        $method->name             = 'Test';
        $method->fee_percentage   = 2.9;
        $method->payment_provider = 'stripe';
        $method->save();
        $method->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 0.30,
        ]));
        $countryTax = new Tax([
            'name'       => 'Payment Tax 1',
            'percentage' => 5,
        ]);
        $countryTax->save();
        $countryTax->countries()->attach(1);

        $method->taxes()->save($countryTax);
        $method->taxes()->save(new Tax([
            'name'       => 'Payment Tax 2',
            'percentage' => 10,
        ]));

        $cart->setPaymentMethod($method);

        $this->assertEquals(10364, round($cart->totals()->totalPostTaxes()));
        $this->assertEquals(330, round($cart->totals()->paymentTotal()->totalPreTaxes()));
        $this->assertEquals(34, round($cart->totals()->paymentTotal()->totalTaxes()));
        $this->assertEquals(364, round($cart->totals()->paymentTotal()->totalPostTaxes()));

        $this->assertEquals(1, $cart->totals()->taxes()->count());
    }

    /**
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
