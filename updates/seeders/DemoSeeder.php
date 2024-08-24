<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Updates\Seeders\Tables\BrandTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerGroupTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomFieldTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ProductTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PropertyTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ReviewCategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ServiceTableSeeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        app()->call(CustomerGroupTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(CustomerTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(BrandTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(CustomFieldTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(ReviewCategoryTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(PropertyTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(CategoryTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(ProductTableSeeder::class, ['useDemo' => true], 'run');
        app()->call(ServiceTableSeeder::class, ['useDemo' => true], 'run');
    }
}
