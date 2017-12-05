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
}
