<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Classes\Pricing;

use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Variant;

class PriceBagTest extends BasePriceBagTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test if the PriceBag calculates the price correctly for a single product.
     *
     * Results
     *      exclusive   = 1000.00
     *      inclusive   = 1000.00
     *
     * @return void
     */
    public function test_calculate_price_for_single_product()
    {
        $price    = ['CHF' => 200, 'EUR' => 240];
        $product  = $this->getProduct($price);
        $quantity = 5;

        // Create Cart
        $cart = $this->getCart($product);
        $cart->addProduct($product, $quantity);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check if price matches
        $this->assertEquals(
            intval($quantity * $price['CHF']) * 100,
            $bag->totalExclusive()->toInt()
        );
        $this->assertEquals(
            intval($quantity * $price['CHF']) * 100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for a single product with service option
     * and an additional tax applied.
     *
     * Results
     *      exclusive   = 545.46
     *      tax         =  54.54
     *      inclusive   = 600.00
     *
     * @return void
     */
    public function test_calculate_price_for_single_product_with_service_option()
    {
        $tax = $this->getTax('Test 1', 10);
        $price = ['CHF' => 200, 'EUR' => 240];
        $quantity = 2;
        
        // Create Service
        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach($tax->id);

        $option = ServiceOption::create([
            'name' => 'Test Option',
            'service_id' => $service->id,
        ]);
        $option->prices()->save(new Price([
            'currency_id' => 2,
            'price'       => 100,
        ]));

        // Create Product
        $product = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);
        $cart->reloadRelations('products');

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            54546,
            $bag->totalExclusive()->toInt()
        );
        $this->assertEquals(
            5454,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            60000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for a single product with service option
     * using 2 different taxes applied.
     *
     * Results
     *      exclusive   = 488.64
     *      tax         = 111.36
     *      inclusive   = 600.00
     *
     * @return void
     */
    public function test_calculate_price_using_multiple_taxes_on_service_option()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 50);
        $price = ['CHF' => 200, 'EUR' => 240];
        $quantity = 2;

        // Create Service
        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach([$tax1->id, $tax2->id]);

        // Create Options
        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 2,
            'price'       => 100,
        ]));

        // Create Product
        $product = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax1->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);
        $cart->reloadRelations('products');

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            48864,
            $bag->totalExclusive()->toInt()
        );
        $this->assertEquals(
            11136,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            60000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for multiple products.
     *
     * Results
     *      inclusive   = 2000.00
     *
     * @return void
     */
    public function test_calculate_price_on_multiple_products()
    {
        $price = ['CHF' => 200, 'EUR' => 240];
        $halfPrice = ['CHF' => 100, 'EUR' => 120];
        $quantity = 5;

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->addProduct($this->getProduct($halfPrice), $quantity * 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            (($quantity * $price['CHF']) + ($quantity * 2 * $halfPrice['CHF'])) * 100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates different taxes correctly.
     *
     * Results
     *      exclusive   = 153.84
     *      no. taxes   = 2
     *      tax 1       =  15.38        (10 %)
     *      tax 2       =  30.77        (20 %)
     *      taxes       =  46.15
     *      inclusive   = 200.00
     *
     * @return void
     */
    public function test_calculate_taxes_correctly()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            15384,
            $bag->totalExclusive()->toInt()
        );
        $this->assertCount(
            2,
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1538,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3077,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4615,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            20000,
            $bag->totalInclusive()->toInt()
        );
    }
 
    /**
     * Test if the PriceBag calculates taxes correctly when passed excluded.
     *
     * Results
     *      exclusive   =  80.00 x 2
     *      no. taxes   = 2
     *      tax 1       =  16.00        (10 %)
     *      tax 2       =  32.00        (20 %)
     *      taxes       =  48.00
     *      inclusive   = 208.00
     *
     * @return void
     */
    public function test_calculate_excluded_taxes_correctly()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            16000,
            $bag->totalExclusive()->toInt()
        );
        $this->assertCount(
            2,
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1600,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3200,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4800,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            20800,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if Shipping Costs are calculated correctly.
     * @return void
     */
    public function test_calculate_shipping_costs()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1538,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3077,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909,
            $bag->shippingTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            5524,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            30000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if forced Shipping Costs are calculated correctly.
     * @return void
     */
    public function test_calculate_forced_shipping_costs()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->taxes()->attach([$tax1->id]);
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);
        $cart->forceShippingPrice($shippingMethod->id, ['CHF' => 200, 'EUR' => 150], 'Enforced Price');

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5152,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            40000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if taxes are calculated correctly, when product is excluded.
     * @return void
     */
    public function test_calculate_taxes_correctly_when_excluded_with_shipping()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax2);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->totalTaxes()
        );
        $this->assertEquals(
            1000,
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909,
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1909,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            21000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if taxes are calculated correctly, when product is excluded.
     * @return void
     */
    public function test_calculate_taxes_correctly_when_excluded_with_quantity()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax2);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 3);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->totalTaxes()
        );
        $this->assertEquals(
            3000,
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1667,
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4667,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            43000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if detailed taxes are calculated correctly on excluded prices.
     * @return void
     */
    public function test_calculate_detailed_taxes_on_excluded_with_shipping()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->totalTaxes()
        );
        $this->assertEquals(
            1000,
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909,
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1909,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            21000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if products weight is calculated correctly.
     * @return void
     */
    public function test_calculate_product_weight_total()
    {
        // Create Products
        $product1 = $this->getProduct(100);
        $product1->weight = 1000;
        $product1->save();

        $product2 = $this->getProduct(100);
        $product2->weight = 500;
        $product2->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product1, 2);
        $cart->addProduct($product1, 1);
        $cart->addProduct($product2, 3);
        $cart->addProduct($product2, 1);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5000,
            $bag->productsWeight()
        );
    }

    /**
     * Test if products weight is calculated correctly.
     * @return void
     */
    public function test_calculate_product_weight_total_using_variants()
    {
        // Create Product
        $product = $this->getProduct(100);
        $product->weight = 1000;
        $product->save();

        // Create Variant
        $variant1 = Variant::create([
            'name'       => 'Variant with Weight',
            'product_id' => $product->id,
            'weight'     => 2000,
            'stock'      => 20,
        ]);
        $variant1->price = ['CHF' => 100, 'EUR' => 150];

        $variant2 = Variant::create([
            'name'       => 'Variant without Weight',
            'product_id' => $product->id,
            'stock'      => 20,
        ]);
        $variant2->price = ['CHF' => 100, 'EUR' => 150];

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variant1);
        $cart->addProduct($product, 1, $variant2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5000,
            $bag->productsWeight()
        );
    }

    /**
     * Test if shipping costs are calculated correctly using a shipping rate.
     * @return void
     */
    public function test_calculate_shipping_cost_using_rate()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->weight = 1000;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Shipping Rate
        $rate = ShippingMethodRate::create([
            'from_weight'        => 2000,
            'shipping_method_id' => $shippingMethod->id,
        ]);
        $rate->price = ['CHF' => 200, 'EUR' => 250];

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            4615,
            $bag->productsTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1818,
            $bag->shippingTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            6433,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            40000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test is variant costs are calculated correctly.
     * @return void
     */
    public function test_calculate_variant_cost()
    {
        // Create Product
        $product = Product::first();
        $product->stackable = true;
        $product->save();
        $product->price = ['CHF' => 20000, 'EUR' => 15000];

        // Create Variant
        $variant = Variant::create([
            'name'       => 'Variant',
            'product_id' => $product->id,
            'stock'      => 20,
        ]);
        $variant->price = ['CHF' => 10000, 'EUR' => 15000];
        $variant = Variant::find($variant->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variant);
        $cart->addProduct($product, 1, $variant);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            300 * 100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if custom field costs are calculates correctly.
     * @return void
     */
    public function test_calculate_custom_fields_costs()
    {
        // Create Custom Field Options
        $sizeA = CustomFieldOption::create([
            'name'       => 'Size A',
            'sort_order' => 1,
        ]);
        $sizeA->price = ['CHF' => 100, 'EUR' => 150];

        $sizeB = CustomFieldOption::create([
            'name'       => 'Size B',
            'sort_order' => 2,
        ]);
        $sizeB->price = ['CHF' => 200, 'EUR' => 150];

        // Create Custom Field
        $field = CustomField::create([
            'name' => 'Size',
            'type' => 'dropdown',
        ]);
        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        // Create Product
        $product = $this->getProduct(['CHF' => 200, 'EUR' => 150]);
        $product->custom_fields()->attach($field);

        // Create Custom Field Values
        $customFieldValueA = CustomFieldValue::create([
            'custom_field_id'        => $field->id,
            'custom_field_option_id' => $sizeA->id,
        ]);
        $customFieldValueB = CustomFieldValue::create([
            'custom_field_id'        => $field->id,
            'custom_field_option_id' => $sizeB->id,
        ]);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            1000 * 100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if custom field fallback costs are calculated correctly.
     * @return void
     */
    public function test_calculate_custom_fields_fallback_costs()
    {
        // Create Custom Field Options
        $sizeA = CustomFieldOption::create([
            'name'       => 'Size A',
            'sort_order' => 1,
        ]);
        $sizeB = CustomFieldOption::create([
            'name'       => 'Size B',
            'sort_order' => 2,
        ]);

        // Create Custom Field
        $field = CustomField::create([
            'name' => 'Size',
            'type' => 'dropdown',
        ]);
        $field->price = ['CHF' => 300, 'EUR' => 150];
        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        // Create Custom Field Values
        $customFieldValueA = CustomFieldValue::create([
            'custom_field_id'        => $field->id,
            'custom_field_option_id' => $sizeA->id,
        ]);
        $customFieldValueB = CustomFieldValue::create([
            'custom_field_id'        => $field->id,
            'custom_field_option_id' => $sizeB->id,
        ]);
        
        // Create Product
        $product = $this->getProduct(200);
        $product->custom_fields()->attach($field);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($product, 1, null, collect([$customFieldValueB]));

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            1500 * 100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if shipping and product taxes are calculated differently.
     * @see https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/684
     * @return void
     */
    public function test_calculate_different_shipping_and_product_taxes()
    {
        $tax1 = $this->getTax('Test 1', 5.5);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(110);
        $product->price_includes_tax = true;
        $product->taxes()->attach($tax1->id);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->price_includes_tax = true;
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 15, 'EUR' => 15];
        $shippingMethod->taxes()->attach($tax2);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            573,
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            250,
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            823,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
    }
}
