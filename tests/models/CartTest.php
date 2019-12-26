<?php namespace OFFLINE\Mall\Tests\Models;

use DB;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;

class CartTest extends PluginTestCase
{
    public function test_it_stacks_products()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product);
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->quantity);
    }

    public function test_it_stacks_variants()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, $variant);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product, 1, $variant);
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->quantity);
    }

    public function test_it_doesnt_stack_unstackable_variants()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, $variant);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product, 1, $variant);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
    }

    public function test_it_stacks_custom_field_variants()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

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

        $field->custom_field_options()->save($sizeA);

        $product->custom_fields()->attach($field);

        $customFieldValue                         = new CustomFieldValue();
        $customFieldValue->custom_field_id        = $field->id;
        $customFieldValue->custom_field_option_id = $sizeA->id;
        $customFieldValue->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, null, collect([$customFieldValue]));

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $this->assertEquals($customFieldValue->id, $cart->products->first()->fresh()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, null, collect([$customFieldValue]));
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products->first()->fresh()->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_products()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);

        $cart->addProduct($product);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
    }

    public function test_it_doesnt_stack_product_custom_field_variants()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

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

        $field->custom_field_options()->save($sizeA);

        $product->custom_fields()->attach($field);

        $customFieldValue                         = new CustomFieldValue();
        $customFieldValue->custom_field_id        = $field->id;
        $customFieldValue->custom_field_option_id = $sizeA->id;
        $customFieldValue->save();

        $cart = new Cart();
        $cart->addProduct($product, 1, null, collect([$customFieldValue]));

        $cart->products->first()->refresh('custom_field_values');
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products->first()->fresh()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, null, collect([$customFieldValue]));
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValue->id, $cart->products[1]->fresh()->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_different_custom_fields_product_variants()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 2;

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        $product->custom_fields()->attach($field);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = new Cart();
        $cart->addProduct($product, 1, null, collect([$customFieldValueA]));

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products->first()->fresh()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products[0]->fresh()->custom_field_values[0]->id);
        $this->assertEquals($customFieldValueB->id, $cart->products[1]->fresh()->custom_field_values[0]->id);
    }

    public function test_it_doesnt_stack_different_custom_field_product_variants_with_text_values()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'text';
        $field->save();

        $product->custom_fields()->attach($field);

        $customFieldValueA                  = new CustomFieldValue();
        $customFieldValueA->custom_field_id = $field->id;
        $customFieldValueA->value           = 'Test';

        $customFieldValueB                  = new CustomFieldValue();
        $customFieldValueB->custom_field_id = $field->id;
        $customFieldValueA->value           = 'Test';

        $cart = new Cart();
        $cart->addProduct($product, 1, null, collect([$customFieldValueA]));

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products->first()->fresh()->custom_field_values[0]->id);

        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->quantity);
        $this->assertEquals($customFieldValueA->id, $cart->products[0]->fresh()->custom_field_values[0]->id);
        $this->assertEquals($customFieldValueB->id, $cart->products[1]->fresh()->custom_field_values[0]->id);
    }

    public function test_it_uses_default_quantity()
    {
        $product                   = Product::first();
        $product->quantity_default = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_enforces_min_quantity()
    {
        $product               = Product::first();
        $product->quantity_min = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_enforces_max_quantity()
    {
        $product               = Product::first();
        $product->quantity_max = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 12);

        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_detects_product_out_of_stock_quantity()
    {
        $this->expectException(OutOfStockException::class);

        $product        = Product::first();
        $product->stock = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 5);

        $this->assertEquals(0, $cart->products->count());
    }

    public function test_it_detects_variant_out_of_stock_quantity()
    {
        $this->expectException(OutOfStockException::class);

        $product = Product::first();
        $product->save();

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 10;
        $variant->save();

        $cart = new Cart();
        $cart->addProduct($product, 11, $variant);

        $this->assertEquals(0, $cart->products->count());
    }

    public function test_it_allows_variant_out_of_stock_purchase()
    {
        $product = Product::first();
        $product->save();

        $variant                               = new Variant();
        $variant->name                         = 'Variant';
        $variant->product_id                   = $product->id;
        $variant->allow_out_of_stock_purchases = true;
        $variant->stock                        = 10;
        $variant->save();

        $cart = new Cart();
        $cart->addProduct($product, 11, $variant);

        $this->assertEquals(1, $cart->products->count());
    }

    public function test_it_allows_product_out_of_stock_purchase()
    {
        $product                               = Product::first();
        $product->allow_out_of_stock_purchases = true;
        $product->stock                        = 10;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 11);

        $this->assertEquals(1, $cart->products->count());
    }

    public function test_it_enforces_max_quantity_on_stacked_products()
    {
        $product               = Product::first();
        $product->stackable    = true;
        $product->quantity_max = 4;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $this->assertEquals(2, $cart->products->first()->quantity);
        $cart->addProduct($product, 3);
        $this->assertEquals(4, $cart->products->first()->quantity);
    }

    public function test_it_increases_the_quantity_for_stacked_products()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $this->assertEquals(2, $cart->products->first()->quantity);
        $cart->addProduct($product, 3);
        $this->assertEquals(5, $cart->products->first()->quantity);
    }

    public function test_the_same_discount_cannot_be_applied_twice()
    {
        $this->expectException(ValidationException::class);

        $product = Product::first();
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $discount       = new Discount();
        $discount->code = 'Test';
        $discount->name = 'Test discount';
        $discount->type = 'rate';
        $discount->rate = 25;
        $discount->save();

        $cart->applyDiscount($discount);
        $cart->applyDiscount($discount);

        $this->assertEquals(1, $cart->discounts->count());
    }

    public function test_only_one_shipping_discount_is_applied()
    {
        $this->expectException(ValidationException::class);

        $product = Product::first();
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $discountA                       = new Discount();
        $discountA->code                 = 'Test';
        $discountA->name                 = 'Test discount';
        $discountA->type                 = 'shipping';
        $discountA->shipping_description = 'Test shipping';
        $discountA->save();

        $discountA->shipping_prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 25,
            'field'       => 'shipping_price',
        ]));

        $discountB = $discountA->replicate();
        $discountB->save();

        $cart->applyDiscount($discountA);
        $cart->applyDiscount($discountB);

        $this->assertEquals(1, $cart->discounts->count());
    }

    public function test_shipping_method_gets_reset_if_it_becomes_unavailable()
    {
        DB::table('offline_mall_shipping_methods')->truncate();

        $product        = Product::first();
        $product->stock = 100;
        $product->save();
        $product->price = [
            'CHF' => 100,
        ];

        $product = Product::first();

        $availableMethod = $this->getShippingMethod();
        $availableMethod->save();

        $availableMethod->available_above_totals()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
            'field'       => 'available_above_totals',
        ]));

        $unavailableMethod = $this->getShippingMethod();
        $unavailableMethod->save();

        $unavailableMethod->available_above_totals()->save(new Price([
            'currency_id' => 1,
            'price'       => 200,
            'field'       => 'available_above_totals',
        ]));

        $cart = new Cart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($unavailableMethod);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(2, $available);
        $this->assertEquals($unavailableMethod->id, $cart->shipping_method_id);

        // Remove one item so that the selected shipping method becomes unavailable
        $cart->setQuantity($cart->products->first()->id, 1);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(1, $available);
        $this->assertEquals($availableMethod->id, $cart->shipping_method_id);
    }

    public function test_shipping_method_gets_nulled_of_none_is_available()
    {
        DB::table('offline_mall_shipping_methods')->truncate();

        $product        = Product::first();
        $product->price = ['CHF' => 100, 'EUR' => 150];

        $product = Product::first();

        $availableMethod = $this->getShippingMethod();
        $availableMethod->save();

        $availableMethod->available_below_totals()->save(new Price([
            'currency_id' => 1,
            'price'       => 200,
            'field'       => 'available_below_totals',
        ]));

        $cart = new Cart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($availableMethod);

        $this->assertEquals($availableMethod->id, $cart->shipping_method_id);

        // The selected shipping method becomes unavailable
        $cart->addProduct($product, 1);

        $available = ShippingMethod::getAvailableByCart($cart);

        $this->assertCount(0, $available);
        $this->assertNull($cart->shipping_method_id);
    }


    public function test_transferred_carts_get_merged()
    {
        $customer = Customer::first();
        $prod1 = Product::find(1);
        $prod2 = Product::find(2);

        // Create an existing Cart for a customer.
        $cart = new Cart();
        $cart->customer_id = $customer->id;
        $cart->save();
        $cart->addProduct($prod1);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->id);

        // Create a new Cart and transfer it to the customer. The Carts
        // have to be merged.
        $cart = new Cart();
        $cart->save();
        $cart->addProduct($prod2);

        $cart->transferToCustomer($customer);

        $cart = $cart->fresh();

        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->id);
        $this->assertEquals(2, $cart->products->last()->id);
    }

    /**
     * @return ShippingMethod
     */
    protected function getShippingMethod(): ShippingMethod
    {
        $availableMethod             = new ShippingMethod();
        $availableMethod->name       = 'Available';
        $availableMethod->sort_order = 1;
        $availableMethod->save();

        $availableMethod->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        return $availableMethod;
    }
}
