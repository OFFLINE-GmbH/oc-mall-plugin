<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\CurrencySettings;
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

        $product                   = Product::first();
        $product->meta_description = 'Test';
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

    public function test_name()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $product->id;
        $variant->save();

        $this->assertEquals('ABC', $product->variants->where('id', $variant->id)->first()->description);
    }

    public function test_price()
    {
        CurrencySettings::set('currencies', [
            ['code' => 'CHF', 'format' => '{{ currency }} {{ price|number_format(2, ".", "\'") }}'],
        ]);

        $product        = Product::first();
        $product->price = 180;
        $product->save();

        $variant             = new Variant();
        $variant->name       = 'ABC';
        $variant->product_id = $product->id;
        $variant->price      = 120;
        $variant->save();

        $this->assertEquals('CHF 120.00', $product->variants->where('id', $variant->id)->first()->price_formatted);
    }

    public function test_name_fallback()
    {
        $product             = Product::first();
        $variant             = new Variant();
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
            $product->variants->where('id', $variant->id)->first()->description
        );
    }

    public function test_name_fallback_ignore_empty()
    {
        $product             = Product::first();
        $variant             = new Variant();
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
            $product->variants->where('id', $variant->id)->first()->description
        );
    }

    public function test_name_fallback_color()
    {
        $product             = Product::first();
        $variant             = new Variant();
        $variant->product_id = $product->id;
        $variant->save();

        $color       = new Property();
        $color->name = 'Color';
        $color->type = 'color';
        $color->save();

        $value              = new PropertyValue();
        $value->property_id = $color->id;
        $value->value       = '#ff0000';
        $variant->property_values()->save($value);

        $this->assertEquals(
            'Color: <span class="mall-color-swatch" style="display: inline-block; width: 10px; height: 10px; background: #ff0000"></span>',
            $product->variants->where('id', $variant->id)->first()->description
        );
    }
}
