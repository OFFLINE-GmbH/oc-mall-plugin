<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;

class CartProductTest extends PluginTestCase
{
    public $product;
    public $variant;
    public $cart;
    public $customFieldValueA;
    protected $address;

    public function setUp()
    {
        parent::setUp();

        $this->address = Address::first();

        $product                   = Product::first();
        $product->meta_description = 'Test';
        $product->save();
        $product->price = ['CHF' => 200, 'EUR' => 150];

        $this->product = $product;

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->name       = 'Variant';
        $variant->price      = null;
        $variant->stock      = 20;
        $variant->save();

        $this->variant = $variant;

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeA->save();
        $sizeA->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field2       = new CustomField();
        $field2->name = 'Label';
        $field2->type = 'text';
        $field2->save();

        $field2->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 300,
        ]));

        $field->custom_field_options()->save($sizeA);

        $this->product->custom_fields()->attach($field);
        $this->product->custom_fields()->attach($field2);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $this->customFieldValueA = $customFieldValueA;

        $customFieldValueB                  = new CustomFieldValue();
        $customFieldValueB->custom_field_id = $field2->id;
        $customFieldValueB->value           = 'Test';

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
        $this->assertEquals('100.0', $transformed[0]['price']['CHF']);
        $this->assertEquals('Size A', $transformed[0]['display_value']);
        $this->assertEquals('Test', $transformed[1]['value']);
        $this->assertEquals('Test', $transformed[1]['display_value']);
        $this->assertEquals('300.0', $transformed[1]['price']['CHF']);
        $this->assertNull($transformed[1]['custom_field_option']);
    }

    public function test_custom_field_price_with_variant()
    {
        $cart = new Cart();
        $cart->save();

        $cart->addProduct($this->product, 1, $this->variant, collect([$this->customFieldValueA]));
        $cartProduct = CartProduct::find($cart->products->first()->id);

        $variant = $cartProduct->variant;

        $this->assertEquals(20000, $variant->price()->integer);
        $this->assertEquals(30000, $cartProduct->price()->integer);
    }

    public function test_service_options_price_calculations_including_tax()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $quantity = 2;

        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach($tax1->id);

        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        $product                     = $this->getProduct(200);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax1->id);

        $cart  = $this->getCart();
        $entry = $cart->addProduct($product, $quantity, null, null, [$option->id]);

        $this->assertEquals(18181.82, round($entry->productPreTaxes, 2));
        $this->assertEquals(1818.18, round($entry->productTaxes, 2));
        $this->assertEquals(20000, $entry->productPostTaxes);

        $this->assertEquals(27272.73, round($entry->pricePreTaxes, 2));
        $this->assertEquals(12727.27, round($entry->taxes, 2));
        $this->assertEquals(40000, $entry->pricePostTaxes);

        $this->assertEquals(9090.91, round($entry->servicePreTaxes, 2));
        $this->assertEquals(909.09, round($entry->serviceTaxes, 2));
        $this->assertEquals(10000, $entry->servicePostTaxes);

        $this->assertEquals(18181.82, round($entry->totalServicePreTaxes, 2));
        $this->assertEquals(1818.18, round($entry->totalServiceTaxes, 2));
        $this->assertEquals(20000, $entry->totalServicePostTaxes);

        $this->assertEquals(54545.45, round($entry->totalPreTaxes, 2));
        $this->assertEquals(5454.55, round($entry->totalTaxes, 2));
        $this->assertEquals(60000, $entry->totalPostTaxes);
    }

    public function test_service_options_price_calculations_excluding_tax()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $quantity = 2;

        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach($tax1->id);

        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        $product                     = $this->getProduct(200);
        $product->price_includes_tax = false;
        $product->save();
        $product->taxes()->attach($tax1->id);

        $cart  = $this->getCart();
        $entry = $cart->addProduct($product, $quantity, null, null, [$option->id]);

        $this->assertEquals(20000, round($entry->productPreTaxes, 2));
        $this->assertEquals(2000, round($entry->productTaxes, 2));
        $this->assertEquals(22000, $entry->productPostTaxes);

        $this->assertEquals(29090.91, round($entry->pricePreTaxes, 2));
        $this->assertEquals(2909.09, round($entry->taxes, 2));
        $this->assertEquals(32000, $entry->pricePostTaxes);

        $this->assertEquals(9090.91, round($entry->servicePreTaxes, 2));
        $this->assertEquals(909.09, round($entry->serviceTaxes, 2));
        $this->assertEquals(10000, $entry->servicePostTaxes);

        $this->assertEquals(18181.82, round($entry->totalServicePreTaxes, 2));
        $this->assertEquals(1818.18, round($entry->totalServiceTaxes, 2));
        $this->assertEquals(20000, $entry->totalServicePostTaxes);

        $this->assertEquals(58181.82, round($entry->totalPreTaxes, 2));
        $this->assertEquals(5818.18, round($entry->totalTaxes, 2));
        $this->assertEquals(64000, $entry->totalPostTaxes);
    }


    protected function getTax($name, int $percentage): Tax
    {
        $tax1             = new Tax();
        $tax1->name       = $name;
        $tax1->percentage = $percentage;
        $tax1->save();

        return $tax1;
    }

    protected function getCart()
    {
        $cart = new Cart();
        $cart->shipping_address_id = $this->address->id;
        $cart->save();

        return $cart;
    }


    protected function getProduct($price)
    {
        if (is_int($price)) {
            $price = ['CHF' => $price, 'EUR' => $price];
        }

        $product = Product::first()->replicate(['category_id']);
        $product->save();
        $product->price = $price;

        // Reload everything to prevent stale relationships.
        return Product::find($product->id);
    }

}

