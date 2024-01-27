<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use DB;
use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Product;
use System\Classes\PluginManager;
use System\Classes\VersionManager;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if (app()->environment() != 'testing') {
            return;
        }

        $product = new Product();
        $product->name = 'Test';

        $product->slug = 'test';
        $product->stock = 20;
        $product->save();
        $product->price = ['CHF' => 20, 'EUR' => 30];

        $product = new Product();
        $product->name = 'Test 2';

        $product->slug = 'test-2';
        $product->stock = 90;
        $product->save();
        $product->price = ['CHF' => 30, 'EUR' => 40];
    }
}