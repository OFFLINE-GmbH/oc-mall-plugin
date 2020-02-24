<?php

namespace OFFLINE\Mall\Tests\Classes\Totals;

use Auth;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;

class TotalsCalculatorTest extends PluginTestCase
{
    protected $address;

    public function setUp()
    {
        parent::setUp();

        $this->address = Address::first();
    }

    public function test_it_works_for_a_single_product()
    {
        $quantity = 5;
        $price    = ['CHF' => 20000, 'EUR' => 24000];

        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals($quantity * $price['CHF'] * 100, $calc->totalPostTaxes());
    }

    public function test_it_works_for_a_single_product_with_service_options()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $quantity = 2;
        $price    = ['CHF' => 200, 'EUR' => 240];

        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach($tax1->id);

        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        $product                     = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax1->id);

        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);

        $cart->reloadRelations('products');

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(54545.45, round($calc->totalPreTaxes(), 2));
        $this->assertEquals(5454.55, round($calc->totalTaxes(), 2));
        $this->assertEquals(60000, $calc->totalPostTaxes());
    }

    public function test_it_works_for_multiple_products_with_service_options()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 50);

        $quantity = 2;
        $price    = ['CHF' => 200, 'EUR' => 240];

        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach([$tax1->id, $tax2->id]);

        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 100,
        ]));

        $product                     = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax1->id);

        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);

        $cart->reloadRelations('products');

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(60000, $calc->productPostTaxes());
        $this->assertEquals(11136.36, round($calc->productTaxes(), 2));
        $this->assertEquals(48863.64, round($calc->productPreTaxes(), 2));
    }

    public function test_it_works_for_multiple_products()
    {
        $quantity  = 5;
        $price     = ['CHF' => 20000, 'EUR' => 24000];
        $halfPrice = ['CHF' => 10000, 'EUR' => 12000];

        $cart = $this->getCart();

        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->addProduct($this->getProduct($halfPrice), $quantity * 2);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(
            (($quantity * $price['CHF']) + ($quantity * 2 * $halfPrice['CHF'])) * 100,
            $calc->totalPostTaxes()
        );
    }

    public function test_it_calculates_taxes_included()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock              = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20000, $calc->totalPostTaxes());
        $this->assertEquals(4615.38, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1538, round($calc->taxes()[0]->total()));
        $this->assertEquals(3077, round($calc->taxes()[1]->total()));
    }

    public function test_it_calculates_taxes_included_on_amount_after_discount_applied()
    {
        $this->markTestSkipped('This test covers an open bug, @see https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/423');

        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock              = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->trigger = 'code';
        $discount->name    = 'Test discount';
        $discount->type    = 'fixed_amount';
        $discount->save();
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(10000, $calc->totalPostTaxes());
        $this->assertEquals(2307.69, round($calc->totalTaxes(), 2));
        $this->assertEquals(769, round($calc->taxes()[0]->total()));
        $this->assertEquals(1538.5, round($calc->taxes()[1]->total()));
    }

    public function test_it_calculates_taxes_included_on_zero_amount_after_discount_applied()
    {
        $this->markTestSkipped('This test covers an open bug, @see https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/423');

        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock              = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->trigger = 'code';
        $discount->name    = 'Test discount';
        $discount->type    = 'rate';
        $discount->rate    = 100;
        $discount->save();

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(0, $calc->totalPostTaxes());
        $this->assertEquals(0, round($calc->totalTaxes(), 2));
        $this->assertEquals(0, round($calc->taxes()[0]->total()));
        $this->assertEquals(0, round($calc->taxes()[1]->total()));
    }

    public function test_it_calculates_taxes_with_different_taxes_and_discount()
    {
        $this->markTestSkipped('This test covers an open bug, @see https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/423');

        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 5);
        $tax3 = $this->getTax('Test 3', 15);

        $product                     = $this->getProduct(115);
        $product->price_includes_tax = true;
        $product->stock              = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 1);

        $product                     = $this->getProduct(57.50);
        $product->price_includes_tax = true;
        $product->stock              = 10;
        $product->taxes()->attach([$tax3->id]);
        $product->save();

        $cart->addProduct($product, 1);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->trigger = 'code';
        $discount->name    = 'Test discount';
        $discount->type    = 'fixed_amount';
        $discount->save();
        $discount->amounts()->save(new Price([
            'price'       => 50,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(12250, $calc->totalPostTaxes());
        $this->assertEquals(1837.5, round($calc->totalTaxes(), 2));
        $this->assertEquals(816.666667, round($calc->taxes()[0]->total()));
        $this->assertEquals(408.333333, round($calc->taxes()[1]->total()));
        $this->assertEquals(612.5, round($calc->taxes()[2]->total()));
    }

    public function test_it_calculates_taxes_excluded()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20800, $calc->totalPostTaxes());
        $this->assertEquals(4800, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1600, $calc->taxes()[0]->total());
        $this->assertEquals(3200, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_taxes_excluded_with_discount()
    {
        $this->markTestSkipped('This test covers an open bug, @see https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/423');

        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->trigger = 'code';
        $discount->name    = 'Test discount';
        $discount->type    = 'fixed_amount';
        $discount->save();
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(10800, $calc->totalPostTaxes());
        $this->assertEquals(2400, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(800, $calc->taxes()[0]->total());
        $this->assertEquals(1600, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_taxes_excluded_on_amount_after_discount_applied()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20800, $calc->totalPostTaxes());
        $this->assertEquals(4800, round($calc->totalTaxes(), 2));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1600, $calc->taxes()[0]->total());
        $this->assertEquals(3200, $calc->taxes()[1]->total());
    }

    public function test_it_calculates_shipping_cost()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(30000, $calc->totalPostTaxes());
        $this->assertEquals(5524, round($calc->totalTaxes()));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(2448, round($calc->taxes()[0]->total()));
        $this->assertEquals(3077, round($calc->taxes()[1]->total()));
    }


    public function test_it_calculates_enforced_shipping_cost()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->taxes()->attach([$tax1->id]);
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $cart->forceShippingPrice($shippingMethod->id, ['CHF' => 200, 'EUR' => 150], 'Enforced Price');

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(40000, $calc->totalPostTaxes());
        $this->assertEquals(5152, round($calc->totalTaxes()));
    }

    public function test_it_calculates_taxes()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 10);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 1);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax2);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(21000, $calc->totalPostTaxes());
        $this->assertEquals(1909, round($calc->totalTaxes()));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(1000, round($calc->taxes()[0]->total()));
        $this->assertEquals(909, round($calc->taxes()[1]->total()));
    }

    public function test_it_calculates_taxes_with_quantity()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 3);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax2);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(43000, $calc->totalPostTaxes());
        $this->assertEquals(4667, round($calc->totalTaxes()));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(3000, round($calc->taxes()[0]->total()));
        $this->assertEquals(1667, round($calc->taxes()[1]->total()));
    }

    public function test_it_consolidates_taxes()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 1);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(21000, $calc->totalPostTaxes());
        $this->assertEquals(1909, round($calc->totalTaxes()));
        $this->assertCount(1, $calc->taxes());
        $this->assertEquals(1909, round($calc->taxes()[0]->total()));
    }

    public function test_it_calculates_detailed_taxes()
    {
        $tax1 = $this->getTax('Test 1', 10);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 1);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(21000, $calc->totalPostTaxes());
        $this->assertEquals(1909, round($calc->totalTaxes()));
        $this->assertCount(2, $calc->detailedTaxes());
        $this->assertEquals(1000, round($calc->detailedTaxes()[0]->total()));
        $this->assertEquals(909, round($calc->detailedTaxes()[1]->total()));
    }

    public function test_it_calculates_weight_total()
    {
        $product         = $this->getProduct(100);
        $product->weight = 1000;
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->addProduct($product, 1);

        $product         = $this->getProduct(100);
        $product->weight = 500;
        $product->save();

        $cart->addProduct($product, 3);
        $cart->addProduct($product, 1);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(5000, $calc->weightTotal());
    }

    public function test_it_calculates_weight_total_with_variants()
    {
        $product         = $this->getProduct(100);
        $product->weight = 1000;
        $product->save();

        $variantWeight             = new Variant();
        $variantWeight->name       = 'Variant with Weight';
        $variantWeight->product_id = $product->id;
        $variantWeight->price      = ['CHF' => 100, 'EUR' => 150];
        $variantWeight->weight     = 2000;
        $variantWeight->stock      = 20;
        $variantWeight->save();

        $variantNoWeight             = new Variant();
        $variantNoWeight->name       = 'Variant without Weight';
        $variantNoWeight->product_id = $product->id;
        $variantNoWeight->price      = ['CHF' => 100, 'EUR' => 150];
        $variantNoWeight->stock      = 20;
        $variantNoWeight->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variantWeight);
        $cart->addProduct($product, 1, $variantNoWeight);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(5000, $calc->weightTotal());
    }

    public function test_it_calculates_shipping_cost_with_special_rates()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->weight             = 1000;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];

        $rate                     = new ShippingMethodRate();
        $rate->from_weight        = 2000;
        $rate->shipping_method_id = $shippingMethod->id;
        $rate->save();
        $rate->price = ['CHF' => 200, 'EUR' => 250];

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(40000, $calc->totalPostTaxes());
        $this->assertEquals(6434, round($calc->totalTaxes()));
        $this->assertCount(2, $calc->taxes());
        $this->assertEquals(3357, round($calc->taxes()[0]->total()));
        $this->assertEquals(3077, round($calc->taxes()[1]->total()));
    }

    public function test_it_calculates_variant_cost()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();
        $product->price = ['CHF' => 20000, 'EUR' => 15000];

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = ['CHF' => 10000, 'EUR' => 15000];

        $variant = Variant::find($variant->id);

        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variant);
        $cart->addProduct($product, 1, $variant);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(300 * 100, $calc->totalPostTaxes());
    }

    public function test_it_calculates_custom_fields_cost()
    {
        $product = $this->getProduct(['CHF' => 200, 'EUR' => 150]);

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 1;

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        $sizeA->price = ['CHF' => 100, 'EUR' => 150];
        $sizeB->price = ['CHF' => 200, 'EUR' => 150];

        $product->custom_fields()->attach($field);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = $this->getCart();
        $cart->addProduct($product, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(1000 * 100, $calc->totalPostTaxes());
    }


    public function test_it_calculates_custom_field_fallback_cost()
    {
        $product = $this->getProduct(200);

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 1;

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();
        $field->price = ['CHF' => 300, 'EUR' => 150];

        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        $product->custom_fields()->attach($field);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = $this->getCart();
        $cart->addProduct($product, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(1500 * 100, $calc->totalPostTaxes());
    }

    public function test_it_applies_fixed_discounts()
    {
        $quantity = 5;
        $price    = ['CHF' => 20000, 'EUR' => 24000];

        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->trigger = 'code';
        $discount->name    = 'Test discount';
        $discount->type    = 'fixed_amount';
        $discount->save();
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(($quantity * $price['CHF'] * 100) - 10000, $calc->totalPostTaxes());
    }

    public function test_it_applies_rate_discounts()
    {
        $quantity = 5;
        $price    = ['CHF' => 20000, 'EUR' => 24000];

        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->name    = 'Test discount';
        $discount->trigger = 'code';
        $discount->type    = 'rate';
        $discount->rate    = 50;
        $discount->save();

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(($quantity * $price['CHF'] * 100) / 2, $calc->totalPostTaxes());
    }

    public function test_it_applies_rate_discounts_always_to_base_price()
    {
        $quantity = 5;
        $price    = ['CHF' => 20000, 'EUR' => 24000];

        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);

        $discountA          = new Discount();
        $discountA->name    = 'Test discount';
        $discountA->trigger = 'code';
        $discountA->type    = 'rate';
        $discountA->rate    = 25;
        $discountA->save();

        $discountB       = $discountA->replicate();
        $discountB->code = 'xxx';
        $discountB->save();

        $cart->applyDiscount($discountA);
        $cart->applyDiscount($discountB);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(($quantity * $price['CHF'] * 100) / 2, $calc->totalPostTaxes());
    }

    public function test_it_applies_alternate_shipping_price_discounts()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        $product                     = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->prices()->save(new Price([
            'price'       => 200,
            'currency_id' => 1,
        ]));

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $discount                       = new Discount();
        $discount->code                 = 'Test';
        $discount->name                 = 'Test discount';
        $discount->trigger              = 'code';
        $discount->type                 = 'shipping';
        $discount->shipping_description = 'Test shipping';
        $discount->save();

        $discount->shipping_prices()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
            'field'       => 'shipping_prices',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(30000, $calc->totalPostTaxes());
        $this->assertEquals(5524, round($calc->totalTaxes()));
    }

    public function test_it_applies_fixed_amount_discount_only_when_given_total_is_reached()
    {
        $product = $this->getProduct(100);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->name    = 'Test discount';
        $discount->type    = 'fixed_amount';
        $discount->trigger = 'total';
        $discount->save();

        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 1,
            'field'       => 'totals_to_reach',
        ]));
        $discount->amounts()->save(new Price([
            'price'       => 150,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20000, $calc->totalPostTaxes());

        $cart->addProduct($product);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(15000, $calc->totalPostTaxes());
    }

    public function test_it_applies_rate_discounts_only_when_given_total_is_reached()
    {
        $product = $this->getProduct(100);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $discount          = new Discount();
        $discount->code    = 'Test';
        $discount->name    = 'Test discount';
        $discount->type    = 'rate';
        $discount->rate    = 50;
        $discount->trigger = 'total';
        $discount->save();

        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 1,
            'field'       => 'totals_to_reach',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20000, $calc->totalPostTaxes());

        $cart->addProduct($product);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(15000, $calc->totalPostTaxes());
    }

    public function test_it_applies_alternate_shipping_price_discounts_only_when_given_total_is_reached()
    {
        $product = $this->getProduct(100);
        $product->save();

        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 200, 'EUR' => 150];

        $cart->setShippingMethod($shippingMethod);

        $discount                       = new Discount();
        $discount->code                 = 'Test';
        $discount->name                 = 'Test discount';
        $discount->type                 = 'shipping';
        $discount->shipping_description = 'Test shipping';
        $discount->trigger              = 'total';
        $discount->save();

        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 1,
            'field'       => 'totals_to_reach',
        ]));
        $discount->shipping_prices()->save(new Price([
            'price'       => 0,
            'currency_id' => 1,
            'field'       => 'shipping_prices',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(40000, $calc->totalPostTaxes());

        $cart->addProduct($product);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(30000, $calc->totalPostTaxes());
    }

    public function test_it_applies_fixed_amount_discount_only_when_needed_product_is_in_cart()
    {
        $productA = $this->getProduct(100);
        $productA->save();
        $productB = $this->getProduct(100);
        $productB->save();

        $cart = $this->getCart();
        $cart->addProduct($productA, 2);

        $discount             = new Discount();
        $discount->code       = 'xxxx';
        $discount->name       = 'Test discount';
        $discount->type       = 'fixed_amount';
        $discount->trigger    = 'product';
        $discount->product_id = $productB->id;
        $discount->save();

        $discount->amounts()->save(new Price([
            'price'       => 150,
            'currency_id' => 1,
            'field'       => 'amounts',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20000, $calc->totalPostTaxes());

        $cart->addProduct($productB);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(15000, $calc->totalPostTaxes());
    }

    public function test_it_applies_rate_discounts_only_when_needed_product_is_in_cart()
    {
        $productA = $this->getProduct(100);
        $productA->save();
        $productB = $this->getProduct(100);
        $productB->save();

        $cart = $this->getCart();
        $cart->addProduct($productA, 2);

        $discount             = new Discount();
        $discount->code       = 'Test';
        $discount->name       = 'Test discount';
        $discount->type       = 'rate';
        $discount->rate       = 50;
        $discount->trigger    = 'product';
        $discount->product_id = $productB->id;
        $discount->save();

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(20000, $calc->totalPostTaxes());

        $cart->addProduct($productB);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(15000, $calc->totalPostTaxes());
    }

    public function test_it_applies_alternate_shipping_price_discounts_only_when_needed_product_is_in_cart()
    {
        $productA = $this->getProduct(100);
        $productA->save();
        $productB = $this->getProduct(100);
        $productB->save();

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $productA->id;
        $variant->price      = ['CHF' => 100, 'EUR' => 150];
        $variant->stock      = 20;
        $variant->save();

        $cart = $this->getCart();
        $cart->addProduct($productA, 2, $variant);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 200, 'EUR' => 150];

        $cart->setShippingMethod($shippingMethod);

        $discount                       = new Discount();
        $discount->code                 = 'Test';
        $discount->name                 = 'Test discount';
        $discount->type                 = 'shipping';
        $discount->shipping_description = 'Test shipping';
        $discount->trigger              = 'product';
        $discount->product_id           = $productB->id;
        $discount->save();

        $discount->shipping_prices()->save(new Price([
            'price'       => 0,
            'currency_id' => 1,
            'field'       => 'shipping_price',
        ]));

        $cart->applyDiscount($discount);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(40000, $calc->totalPostTaxes());

        $cart->addProduct($productB);

        $calc = new TotalsCalculator(TotalsCalculatorInput::fromCart($cart));
        $this->assertEquals(30000, $calc->totalPostTaxes());
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

    protected function getCart(): Cart
    {
        $cart                      = new Cart();
        $cart->shipping_address_id = $this->address->id;
        $cart->save();

        return $cart;
    }

    protected function getTax($name, int $percentage): Tax
    {
        $tax1             = new Tax();
        $tax1->name       = $name;
        $tax1->percentage = $percentage;
        $tax1->save();

        return $tax1;
    }
}
