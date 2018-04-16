<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\Product;
use PluginTestCase;

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
        $priceInt       = ['CHF' => 2050, 'EUR' => 8050];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $product        = Product::first();
        $product->price = $price;
        $product->save();

        $this->assertEquals($price, $product->price);
        $this->assertEquals(json_encode($priceInt), $product->getOriginal('price'));
        $this->assertEquals($priceFormatted, $product->price_formatted);
        $this->assertEquals(80.50, $product->priceInCurrency('EUR'));
        $this->assertEquals(20.50, $product->priceInCurrency());
        $this->assertEquals(2050, $product->priceInCurrencyInteger('CHF'));
        $this->assertEquals('CHF 20.50', $product->priceInCurrencyFormatted('CHF'));
    }
}
