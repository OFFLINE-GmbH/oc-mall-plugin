<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Tests\PluginTestCase;

class ProductTest extends PluginTestCase
{
    public function test_custom_field_relationship()
    {
        $product = Product::first();
        $field   = CustomField::first();

        $product->custom_fields()->save($field);

        $this->assertEquals('Test', $product->fresh()->custom_fields->first()->name);
    }

    public function test_price_accessors()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $product        = Product::first();
        $product->save();
        $product->price = $price;

        $product        = Product::first();

        $this->assertEquals($priceFormatted['CHF'], $product->priceInCurrencyFormatted());
        $this->assertEquals(80.50, $product->priceInCurrency('EUR'));
        $this->assertEquals(20.50, $product->priceInCurrency());
        $this->assertEquals(2050, $product->priceInCurrencyInteger('CHF'));
        $this->assertEquals('CHF 20.50', $product->priceInCurrencyFormatted('CHF'));
    }
}
