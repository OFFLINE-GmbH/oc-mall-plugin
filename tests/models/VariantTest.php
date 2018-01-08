<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Product;
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

}
