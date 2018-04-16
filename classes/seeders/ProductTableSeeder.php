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
                $product->price = ['CHF' => 20, 'EUR' => 30];
                $product->save();
            } catch (\Throwable $e) {
                dd($e);
            }
        }
    }
}
