<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\Location\Models\Country;

class ShippingMethodTest extends PluginTestCase
{
    public function test_it_returns_available_by_min_total()
    {
        $product = $this->getProduct(99);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod = $this->getMethod();
        $availableMethod->available_below_total()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'available_below_total',
        ]));

        $unavailableMethod = $this->getMethod();
        $unavailableMethod->available_below_total()->save(new Price([
            'price'       => 50,
            'currency_id' => 1,
            'field'       => 'available_below_total',
        ]));

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    public function test_it_returns_available_by_max_total()
    {
        $product = $this->getProduct(100);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod = $this->getMethod();
        $availableMethod->available_above_total()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'available_above_total',
        ]));

        $unavailableMethod = $this->getMethod();
        $unavailableMethod->available_above_total()->save(new Price([
            'price'       => 200,
            'currency_id' => 1,
            'field'       => 'available_above_total',
        ]));

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $available->first()->id);
    }

    public function test_it_returns_available_by_min_and_max_total()
    {
        $product = $this->getProduct(100);

        $cart = new Cart();
        $cart->addProduct($product, 1);

        $availableMethod = $this->getMethod();
        $availableMethod->available_below_total()->save(new Price([
            'price'       => 200,
            'currency_id' => 1,
            'field'       => 'available_below_total',
        ]));
        $availableMethod->available_above_total()->save(new Price([
            'price'       => 50,
            'currency_id' => 1,
            'field'       => 'available_above_total',
        ]));

        $unavailableMethod = $this->getMethod();
        $unavailableMethod->available_below_total()->save(new Price([
            'price'       => 120,
            'currency_id' => 1,
            'field'       => 'available_below_total',
        ]));
        $unavailableMethod->available_above_total()->save(new Price([
            'price'       => 110,
            'currency_id' => 1,
            'field'       => 'available_above_total',
        ]));

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
        $availableMethod->sort_order = 1;
        $availableMethod->save();

        $availableMethod->prices()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'price',
        ]));

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
        $product->save();
        $product->price = ['CHF' => $price, 'EUR' => 150];

        return Product::first();
    }
}
