<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;
use System\Models\File;

class VariantTest extends PluginTestCase
{
    public $product;
    public $variant;

    public function setUp()
    {
        parent::setUp();

        $product                     = new Product();
        $product->name               = 'Test';
        $product->meta_description   = 'Test';
        $product->slug               = 'test';
        $product->stock              = 20;
        $product->price_includes_tax = true;
        $product->save();
        $product->price = ['CHF' => 20, 'EUR' => 30];

        $this->product = Product::find($product->id);

        $variantSet             = new ImageSet();
        $variantSet->name       = 'Variant Images';
        $variantSet->product_id = $this->product->id;
        $variantSet->save();

        $productSet              = new ImageSet();
        $productSet->name        = 'Product Images';
        $productSet->is_main_set = true;

        $this->product->image_sets()->save($productSet);

        $file               = new File();
        $file->disk_name    = 'variant.jpg';
        $file->file_name    = 'variant.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $variantSet->images()->save($file);

        $file               = new File();
        $file->disk_name    = 'product.jpg';
        $file->file_name    = 'product.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $productSet->images()->save($file);

        $variant               = new Variant();
        $variant->product_id   = $product->id;
        $variant->stock        = 20;
        $variant->name         = 'Variant';
        $variant->image_set_id = $variantSet->id;
        $variant->save();

        $this->variant = Variant::find($variant->id);

        $this->variant->refresh('image_sets');
        $this->product->refresh('image_sets');
    }

    public function test_it_inherits_parent_fields()
    {
        $this->assertEquals($this->product->meta_description, $this->variant->meta_description);
        $this->assertEquals($this->product->price_includes_tax, $this->variant->price_includes_tax);
        $this->assertEquals('Variant', $this->variant->name);
    }

    public function test_it_inherits_parent_relations()
    {
        $tax             = new Tax();
        $tax->name       = 'Tax';
        $tax->percentage = 8;
        $this->product->taxes()->save($tax);

        $this->assertEquals($this->product->taxes->pluck('id'), $this->variant->taxes->pluck('id'));
    }

    public function test_it_keeps_own_files()
    {
        $this->assertEquals('variant.jpg', $this->variant->main_image->disk_name);
        $this->assertEquals('product.jpg', $this->product->main_image->disk_name);
    }

    public function test_it_inherits_files()
    {
        \DB::table('offline_mall_product_variants')
           ->where('id', $this->variant->id)
           ->update(['image_set_id' => null]);

        $this->variant = Variant::find($this->variant->id);

        $this->assertEquals('product.jpg', $this->variant->main_image->disk_name);
        $this->assertEquals('product.jpg', $this->product->main_image->disk_name);
    }

    public function test_it_inherits_file_accessors_for_images()
    {
        $file               = new File();
        $file->disk_name    = 'additional.jpg';
        $file->file_name    = 'additional.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $this->product->main_image_set->images()->save($file);

        \DB::table('offline_mall_product_variants')
           ->where('id', $this->variant->id)
           ->update(['image_set_id' => null]);

        $this->variant = Variant::find($this->variant->id);

        $this->assertEquals(1, $this->variant->images->count());
        $this->assertNotNull($this->variant->main_image);
        $this->assertNotNull($this->variant->image);
        $this->assertTrue($this->variant->all_images->pluck('disk_name')->contains('additional.jpg'));
        $this->assertEquals(2, $this->variant->all_images->count());
    }

    public function test_name_is_not_used_as_property_description()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->stock      = 20;
        $variant->product_id = $product->id;
        $variant->save();

        $this->assertEquals('', $product->variants->where('id', $variant->id)->first()->properties_description);
    }

    public function test_price_accessors()
    {
        $priceInt       = ['CHF' => 2050, 'EUR' => 8050];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = $priceInt;

        $variant = Variant::find($variant->id);

        $this->assertEquals($priceFormatted, $variant->price->toArray());
        $this->assertEquals(80.50, $variant->price('EUR')->decimal);
        $this->assertEquals(20.50, $variant->price()->decimal);
        $this->assertEquals(2050, $variant->price('CHF')->integer);
        $this->assertEquals('CHF 20.50', (string)$variant->price('CHF'));
    }

    public function test_price_accessors_are_inherited()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->stock      = 20;
        $variant->product_id = $this->product->id;
        $variant->save();

        $this->assertEquals($priceFormatted, $variant->price->toArray());
        $this->assertEquals(80.50, $variant->price('EUR')->decimal);
        $this->assertEquals(20.50, $variant->price()->decimal);
        $this->assertEquals(2050, $variant->price('CHF')->integer);
        $this->assertEquals('CHF 20.50', (string)$variant->price('CHF'));
    }

    public function test_price_accessors_in_specific_currency_are_inherited()
    {
        $price = ['CHF' => 100, 'EUR' => 90];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = ['CHF' => 11000, 'EUR' => null];

        $variant = Variant::find($variant->id);
        $this->assertEquals(125, (int)$variant->price('EUR')->decimal);
        $this->assertEquals(110.00, $variant->price('CHF')->decimal);
    }

    public function test_price_accessors_for_other_fields_are_not_inherited()
    {
        $price = ['CHF' => 100, 'EUR' => 90];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = ['CHF' => 11000, 'EUR' => 10000];

        $variant = Variant::find($variant->id);
        $this->assertEquals(null, (int)$variant->oldPrice('EUR')->decimal);
        $this->assertEquals(null, $variant->oldPrice('CHF')->decimal);
    }

    public function test_explicit_null_price_accessors_are_inherited()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = ['CHF' => null, 'EUR' => null];

        $variant = Variant::find($variant->id);

        $this->assertEquals($priceFormatted, $variant->price->toArray());
        $this->assertEquals(80.50, $variant->price('EUR')->decimal);
        $this->assertEquals(20.50, $variant->price()->decimal);
        $this->assertEquals(2050, $variant->price('CHF')->integer);
        $this->assertEquals('CHF 20.50', (string)$variant->price('CHF'));
    }

    public function test_price_accessors_are_inherited_by_currency()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['EUR' => '50.00€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->price = ['EUR' => 5000, 'CHF' => null];

        $variant = Variant::find($variant->id);
        $this->assertEquals($priceFormatted, $variant->price->toArray());
        $this->assertEquals(50, $variant->price('EUR')->decimal);
        $this->assertEquals(20.50, $variant->price()->decimal);
        $this->assertEquals(2050, $variant->price('CHF')->integer);
        $this->assertEquals(5000, $variant->price('EUR')->integer);
    }

    public function test_alternative_price_accessors()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 20;
        $variant->save();
        $variant->additional_prices()->save(new Price([
            'price'             => 20.50,
            'price_category_id' => 1,
            'currency_id'       => 1,
        ]));
        $variant->additional_prices()->save(new Price([
            'price'             => 80.50,
            'price_category_id' => 1,
            'currency_id'       => 2,
        ]));
        
        $variant->load('additional_prices');

        $this->assertEquals($priceFormatted, $variant->old_price->toArray());
        $this->assertEquals(80.50, $variant->oldPrice('EUR')->decimal);
        $this->assertEquals(20.50, $variant->oldPrice()->decimal);
        $this->assertEquals(2050, $variant->oldPrice('CHF')->integer);
        $this->assertEquals(8050, $variant->oldPrice('EUR')->integer);
    }

    public function test_stock_values()
    {
        $this->product->stock = 100;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->stock      = 0;
        $variant->price      = ['CHF' => null, 'EUR' => null];
        $variant->save();

        $this->assertEquals(0, $variant->stock);
    }

    public function test_name_fallback()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $height             = Property::find(1);
        $value              = new PropertyValue();
        $value->product_id  = $product->id;
        $value->property_id = $height->id;
        $value->value       = 200;
        $variant->property_values()->save($value);

        $width              = Property::find(2);
        $value              = new PropertyValue();
        $value->product_id  = $product->id;
        $value->property_id = $width->id;
        $value->value       = 400;
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Height: 200 mm<br />Width: 400 mm',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }

    public function test_name_fallback_ignore_empty()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $height             = Property::find(1);
        $value              = new PropertyValue();
        $value->property_id = $height->id;
        $value->product_id  = $product->id;
        $value->value       = null;
        $variant->property_values()->save($value);

        $width              = Property::find(2);
        $value              = new PropertyValue();
        $value->property_id = $width->id;
        $value->product_id  = $product->id;
        $value->value       = 400;
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Width: 400 mm',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }

    public function test_name_fallback_color()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $color       = new Property();
        $color->name = 'Color';
        $color->type = 'color';
        $color->save();

        $value              = new PropertyValue();
        $value->property_id = $color->id;
        $value->product_id  = $product->id;
        $value->value       = ['hex' => '#ff0000', 'name' => 'Red'];
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Color: <span class="mall-color-swatch" style="display: inline-block; width: 12px; height: 12px; background: #ff0000" title="Red"></span>',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }
}
