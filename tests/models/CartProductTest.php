<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;

class CartProductTest extends PluginTestCase
{
    public $product;
    public $variant;
    public $cart;
    public $customFieldValueA;

    public function setUp()
    {
        parent::setUp();

        $product                   = Product::first();
        $product->meta_description = 'Test';
        $product->price            = 200;
        $product->save();

        $this->product = $product;

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->name       = 'Variant';
        $variant->price      = null;
        $variant->save();

        $this->variant = $variant;

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->price      = 100;
        $sizeA->sort_order = 1;

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field2        = new CustomField();
        $field2->name  = 'Label';
        $field2->type  = 'text';
        $field2->price = 300;
        $field2->save();

        $field->custom_field_options()->save($sizeA);

        $this->product->custom_fields()->attach($field);
        $this->product->custom_fields()->attach($field2);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $this->customFieldValueA = $customFieldValueA;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field2->id;
        $customFieldValueB->value                  = 'Test';

        $cart = new Cart();
        $cart->save();

        $this->cart = $cart;

        $cart->addProduct($product, 2, null, collect([$customFieldValueA, $customFieldValueB]));
    }


    public function test_custom_field_value_conversion()
    {
        $cartProduct = CartProduct::first();

        $transformed = $cartProduct->convertCustomFieldValues();

        $this->assertEquals(2, $transformed->count());
        $this->assertEquals('100.0', $transformed[0]['price']);
        $this->assertEquals('Size A', $transformed[0]['display_value']);
        $this->assertEquals('100.0', $transformed[0]['price']);
        $this->assertEquals('Test', $transformed[1]['value']);
        $this->assertEquals('Test', $transformed[1]['display_value']);
        $this->assertEquals('300.0', $transformed[1]['price']);
        $this->assertNull($transformed[1]['custom_field_option']);
    }

    public function test_custom_field_price_with_variant()
    {
        $cart = new Cart();
        $cart->save();

        $cart->addProduct($this->product, 1, $this->variant, collect([$this->customFieldValueA]));
        $cartProduct = CartProduct::find($cart->products->first()->id);

        $variant = $cartProduct->variant;

        $this->assertEquals(200, $variant->price);
        $this->assertEquals(30000, $cartProduct->price);
    }
}
