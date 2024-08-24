<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomerGroupPrice;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Models\User;

class CustomerGroupPriceTest extends PluginTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $variant             = new Variant();
        $variant->product_id = Product::first()->id;
        $variant->name       = 'Variant';
        $variant->stock      = 20;
        $variant->save();
    }

    public function test_relationship()
    {
        $price                    = new CustomerGroupPrice();
        $price->price             = 50;
        $price->currency_id       = 2;
        $price->customer_group_id = CustomerGroup::where('code', 'gold')->first()->id;

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
        $price->customer_group_id = CustomerGroup::where('code', 'gold')->first()->id;
        $price->price             = 50;
        $price->currency_id       = 2;

        $product = Product::first();
        $product->customer_group_prices()->add($price);

        $price                    = new CustomerGroupPrice();
        $price->customer_group_id = CustomerGroup::where('code', 'gold')->first()->id;
        $price->price             = 74.00;
        $price->currency_id       = 1;
        $product->customer_group_prices()->add($price);

        $this->assertEquals(2000, $product->price()->integer);

        // Is in customer group
        Auth::login(User::where('email', 'gold_customer@example.tld')->first());

        $this->assertEquals(5000, $product->price()->integer);
        $this->assertEquals(50.00, $product->price()->decimal);
        $this->assertEquals('CHF 50.00', (string)$product->price());

        // Is not in customer group
        Auth::login(User::where('email', '<>', 'gold_customer@example.tld')->first());

        $product->customer_group_prices()->add($price);
        $this->assertEquals(2000, $product->price()->integer);
    }
}
