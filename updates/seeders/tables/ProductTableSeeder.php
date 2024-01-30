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

        $product = Product::create([
            'name' => 'Test',
            'slug' => 'test',
            'stock' => 20,
            'published' => true,
        ]);
        $product->price = ['CHF' => 20, 'EUR' => 30];

        $product = Product::create([
            'name' => 'Test 2',
            'slug' => 'test-2',
            'stock' => 90,
            'published' => true,
        ]);
        $product->price = ['CHF' => 30, 'EUR' => 40];
    }
}
