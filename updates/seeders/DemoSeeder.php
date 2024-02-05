<?php declare(strict_types=1);

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
        $this->call([
            CustomerGroupTableSeeder::class,
            CustomerTableSeeder::class,
            BrandTableSeeder::class,
            CustomFieldTableSeeder::class,
            ReviewCategoryTableSeeder::class,
            PropertyTableSeeder::class,
            CategoryTableSeeder::class,
            ProductTableSeeder::class,
            ServiceTableSeeder::class,
        ], false, [
            'useDemo' => true
        ]);
    }
}
