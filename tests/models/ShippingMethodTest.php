<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use PluginTestCase;
use RainLab\Location\Models\Country;

class ShippingMethodTest extends PluginTestCase
{
    public function test_it_returns_available_by_min_total()
    {
        $product = $this->getProduct(99);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod                        = $this->getMethod();
        $availableMethod->available_below_total = ['CHF' => 100, 'EUR' => 150];
        $availableMethod->save();

        $unavailableMethod                        = $this->getMethod();
        $unavailableMethod->available_below_total = ['CHF' => 50, 'EUR' => 150];
        $unavailableMethod->save();

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    public function test_it_returns_available_by_max_total()
    {
        $product = $this->getProduct(100);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod                        = $this->getMethod();
        $availableMethod->available_above_total = ['CHF' => 100, 'EUR' => 150];
        $availableMethod->save();

        $unavailableMethod                        = $this->getMethod();
        $unavailableMethod->available_above_total = ['CHF' => 200, 'EUR' => 150];
        $unavailableMethod->save();

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    public function test_it_returns_available_by_min_and_max_total()
    {
        $product = $this->getProduct(100);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod                        = $this->getMethod();
        $availableMethod->available_below_total = ['CHF' => 200, 'EUR' => 150];
        $availableMethod->available_above_total = ['CHF' => 50, 'EUR' => 150];
        $availableMethod->save();

        $unavailableMethod                        = $this->getMethod();
        $unavailableMethod->available_below_total = ['CHF' => 120, 'EUR' => 150];
        $unavailableMethod->available_above_total = ['CHF' => 110, 'EUR' => 150];
        $unavailableMethod->save();

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    public function test_it_returns_available_by_destination_country()
    {
        $product = $this->getProduct(100);

        $address             = new Address();
        $address->name       = 'Mr. Miller';
        $address->lines      = 'Street 12';
        $address->zip        = '6003';
        $address->city       = 'Lucerne';
        $address->country_id = Country::where('code', 'CH')->first()->id;
        $address->save();

        $cart = new Cart();
        $cart->addProduct($product, 1);
        $cart->setShippingAddress($address);

        $availableMethod = $this->getMethod();
        $availableMethod->countries()->attach(Country::whereIn('code', ['CH', 'DE', 'AT'])->get());
        $availableMethod->save();

        $unavailableMethod = $this->getMethod();
        $unavailableMethod->countries()->attach(Country::whereIn('code', ['DE', 'AT'])->get());
        $unavailableMethod->save();

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    /**
     * @return ShippingMethod
     */
    protected function getMethod(): ShippingMethod
    {
        $availableMethod             = new ShippingMethod();
        $availableMethod->name       = 'Available';
        $availableMethod->price      = ['CHF' => 100, 'EUR' => 150];
        $availableMethod->sort_order = 1;
        $availableMethod->save();

        return $availableMethod;
    }

    public function setUp()
    {
        parent::setUp();
        \DB::table('offline_mall_shipping_methods')->truncate();
    }

    /**
     * @return mixed
     */
    protected function getProduct(int $price): Product
    {
        $product        = Product::first();
        $product->price = ['CHF' => $price, 'EUR' => 150];
        $product->save();

        return $product;
    }
}
