<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Brand;

class BrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if (!$useDemo) {
            return;
        }
        
        Brand::create([
            'name'        => 'Cruiser Bikes',
            'slug'        => 'cruiser-bikes',
            'description' => trans('offline.mall::demo.brands.cruiser_bikes.description'),
            'website'     => 'https://cruiser.bikes',
            'sort_order'  => 1,
        ]);
    }
}
