<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Product;

class ProductTableSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment() === 'testing') {
            try {
                $product       = new Product();
                $product->name = 'Test';

                $product->slug  = 'test';
                $product->stock = 20;
                $product->save();
                $product->price = ['CHF' => 20, 'EUR' => 30];

                $product       = new Product();
                $product->name = 'Test 2';

                $product->slug  = 'test-2';
                $product->stock = 90;
                $product->save();
                $product->price = ['CHF' => 30, 'EUR' => 40];
            } catch (\Throwable $e) {
                dd($e);
            }
        }
    }
}
