<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;
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
        $product->price              = ['CHF' => 20, 'EUR' => 30];
        $product->save();
        $this->product = $product;

        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->name       = 'Variant';
        $variant->save();

        $this->variant = $variant;
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
        $file               = new File();
        $file->disk_name    = 'variant.jpg';
        $file->file_name    = 'variant.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $this->variant->main_image()->save($file);

        $file               = new File();
        $file->disk_name    = 'product.jpg';
        $file->file_name    = 'product.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $this->product->main_image()->save($file);

        $this->assertEquals('variant.jpg', $this->variant->main_image->disk_name);
        $this->assertEquals('product.jpg', $this->product->main_image->disk_name);
    }

    public function test_it_inherits_files()
    {
        $file               = new File();
        $file->disk_name    = 'product.jpg';
        $file->file_name    = 'product.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $this->product->main_image()->save($file);

        $this->assertEquals('product.jpg', $this->variant->main_image->disk_name);
        $this->assertEquals('product.jpg', $this->product->main_image->disk_name);
    }

    public function test_it_inherits_file_accessors()
    {
        $file               = new File();
        $file->disk_name    = 'product.jpg';
        $file->file_name    = 'product.jpg';
        $file->file_size    = 8;
        $file->content_type = 'image/jpeg';

        $this->product->main_image()->save($file);

        $this->assertEquals('product.jpg', $this->variant->image->disk_name);
        $this->assertEquals('product.jpg', $this->product->image->disk_name);
    }

    public function test_it_inherits_file_accessors_for_images()
    {
        $getFile = function () {
            $file               = new File();
            $file->disk_name    = 'product.jpg';
            $file->file_name    = 'product.jpg';
            $file->file_size    = 8;
            $file->content_type = 'image/jpeg';

            return $file;
        };

        $this->product->main_image()->save($getFile());
        $this->product->images()->save($getFile());

        $this->assertEquals(1, $this->variant->images->count());
        $this->assertNotNull($this->variant->main_image);
        $this->assertEquals(2, $this->variant->all_images->count());
        $this->assertEquals(1, $this->variant->additional_images->count());
    }

    public function test_name_is_not_used_as_property_description()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $product->id;
        $variant->save();

        $this->assertEquals('', $product->variants->where('id', $variant->id)->first()->properties_description);
    }

    public function test_price_accessors()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceInt       = ['CHF' => 2050, 'EUR' => 8050];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->price      = $price;
        $variant->save();

        $this->assertEquals($price, $variant->price);
        $this->assertEquals(json_encode($priceInt), $variant->getOriginal('price'));
        $this->assertEquals($priceFormatted, $variant->price_formatted);
        $this->assertEquals(80.50, $variant->priceInCurrency('EUR'));
        $this->assertEquals(20.50, $variant->priceInCurrency());
        $this->assertEquals(2050, $variant->priceInCurrencyInteger('CHF'));
        $this->assertEquals('CHF 20.50', $variant->priceInCurrencyFormatted('CHF'));
    }

    public function test_price_accessors_are_inherited()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $this->product->id;
        $variant->save();

        $this->assertEquals($price, $variant->price);
        $this->assertEquals($priceFormatted, $variant->price_formatted);
        $this->assertEquals(80.50, $variant->priceInCurrency('EUR'));
        $this->assertEquals(20.50, $variant->priceInCurrency());
        $this->assertEquals(2050, $variant->priceInCurrencyInteger('CHF'));
        $this->assertEquals('CHF 20.50', $variant->priceInCurrencyFormatted('CHF'));
    }

    public function test_price_accessors_are_inherited_by_currency()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '50.00€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->price      = ['EUR' => 50];
        $variant->product_id = $this->product->id;
        $variant->save();

        $this->assertEquals(['CHF' => 20.50, 'EUR' => 50], $variant->price);
        $this->assertEquals($priceFormatted, $variant->price_formatted);
        $this->assertEquals(50, $variant->priceInCurrency('EUR'));
        $this->assertEquals(20.50, $variant->priceInCurrency());
        $this->assertEquals(2050, $variant->priceInCurrencyInteger('CHF'));
        $this->assertEquals(5000, $variant->priceInCurrencyInteger('EUR'));
    }

    public function test_alternative_price_accessors()
    {
        $price          = ['CHF' => 20.50, 'EUR' => 80.50];
        $priceFormatted = ['CHF' => 'CHF 20.50', 'EUR' => '80.50€'];

        $this->product->price = $price;
        $this->product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->old_price  = $price;
        $variant->product_id = $this->product->id;
        $variant->save();

        $this->assertEquals($price, $variant->old_price);
        $this->assertEquals($priceFormatted, $variant->old_price_formatted);
        $this->assertEquals(80.50, $variant->oldPriceInCurrency('EUR'));
        $this->assertEquals(20.50, $variant->oldPriceInCurrency());
        $this->assertEquals(2050, $variant->oldPriceInCurrencyInteger('CHF'));
        $this->assertEquals(8050, $variant->oldPriceInCurrencyInteger('EUR'));
    }

    public function test_name_fallback()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->save();

        $height             = Property::find(1);
        $value              = new PropertyValue();
        $value->property_id = $height->id;
        $value->value       = 200;
        $variant->property_values()->save($value);

        $width              = Property::find(2);
        $value              = new PropertyValue();
        $value->property_id = $width->id;
        $value->value       = 400;
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Height: 200<br />Width: 400',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }

    public function test_name_fallback_ignore_empty()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->save();

        $height             = Property::find(1);
        $value              = new PropertyValue();
        $value->property_id = $height->id;
        $value->value       = null;
        $variant->property_values()->save($value);

        $width              = Property::find(2);
        $value              = new PropertyValue();
        $value->property_id = $width->id;
        $value->value       = 400;
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Width: 400',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }

    public function test_name_fallback_color()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->save();

        $color       = new Property();
        $color->name = 'Color';
        $color->type = 'color';
        $color->save();

        $value              = new PropertyValue();
        $value->property_id = $color->id;
        $value->value       = ['hex' => '#ff0000', 'name' => 'Red'];
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Color: <span class="mall-color-swatch" style="display: inline-block; width: 10px; height: 10px; background: #ff0000" title="Red"></span>',
            $product->variants->where('id', $variant->id)->first()->properties_description
        );
    }
}
