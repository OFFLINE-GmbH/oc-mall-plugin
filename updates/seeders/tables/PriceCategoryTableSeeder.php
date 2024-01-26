<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\PriceCategory;

class PriceCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        PriceCategory::create([
            'code' => 'msrp',
            'name' => 'Manufacturer\'s suggested retail price',
        ]);
    }
}
