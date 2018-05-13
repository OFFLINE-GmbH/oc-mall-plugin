<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomerGroupPrice;
use OFFLINE\Mall\Models\Product;
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
}