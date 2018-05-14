<?php namespace OFFLINE\Mall\Tests\Models;


use OFFLINE\Mall\Classes\Customer\AuthManager;
use RainLab\User\Facades\Auth;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomerGroupPrice;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\User;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;


class CustomerGroupPriceTest extends PluginTestCase
{
    public function setUp()
    {
        parent::setUp();
        $variant             = new Variant();
        $variant->product_id = Product::first()->id;
        $variant->name       = 'Variant';
        $variant->save();

        app()->singleton('user.auth', function () {
            return AuthManager::instance();
        });
    }

    public function test_relationship()
    {
        $price                    = new CustomerGroupPrice();
        $price->price             = ['EUR' => 200, 'CHF' => 50];
        $price->customer_group_id = CustomerGroup::first()->id;

        $product = Product::first();
        $product->customer_group_prices()->add($price);

        $variant = Variant::first();
        $variant->customer_group_prices()->add($price->replicate());

        $this->assertCount(1, $product->customer_group_prices);
        $this->assertCount(1, $variant->customer_group_prices);
    }

    public function test_price_is_loaded_correctly()
    {
        $price                    = new CustomerGroupPrice();
        $price->price             = ['EUR' => 74.00, 'CHF' => 50.00];
        $price->customer_group_id = CustomerGroup::first()->id;

        $product = Product::first();
        $product->customer_group_prices()->add($price);

        $this->assertEquals(2000, $product->priceInCurrencyInteger());

        Auth::login(User::find(1)); // Is in customer group

        $this->assertEquals(5000, $product->priceInCurrencyInteger());
        $this->assertEquals(50.00, $product->priceInCurrency());
        $this->assertEquals('CHF 50.00', $product->priceInCurrencyFormatted());
        $this->assertEquals(['EUR' => 74.00, 'CHF' => 50.00], $product->price);

        Auth::login(User::find(2)); // Is not in customer group

        $product->customer_group_prices()->add($price);
        $this->assertEquals(2000, $product->priceInCurrencyInteger());
    }
}