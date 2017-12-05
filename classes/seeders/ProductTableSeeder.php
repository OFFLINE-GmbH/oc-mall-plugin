<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Product;

class ProductTableSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment() === 'testing') {
            $product        = new Product();
            $product->name  = 'Test';
            $product->slug  = 'test';
            $product->price = 20;
            $product->save();
        }
    }
}