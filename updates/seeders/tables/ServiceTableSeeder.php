<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;

class ServiceTableSeeder extends Seeder
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

        // Warranty
        $warranty = Service::create([
            'name'          => trans('offline.mall::demo.services.warranty.name'),
            'description'   => trans('offline.mall::demo.services.warranty.description'),
        ]);

        $option = ServiceOption::create([
            'name'          => trans('offline.mall::demo.services.warranty.2_years_name'),
            'description'   => trans('offline.mall::demo.services.warranty.2_years_description'),
            'service_id'    => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 49]));

        $option = ServiceOption::create([
            'name'          => trans('offline.mall::demo.services.warranty.3_years_name'),
            'description'   => trans('offline.mall::demo.services.warranty.3_years_description'),
            'service_id'    => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 69]));

        $option = ServiceOption::create([
            'name'          => trans('offline.mall::demo.services.warranty.4_years_name'),
            'description'   => trans('offline.mall::demo.services.warranty.4_years_description'),
            'service_id'  => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 99]));

        // Assembly
        $assembly = Service::create([
            'name'          => trans('offline.mall::demo.services.assembly.name'),
            'description'   => trans('offline.mall::demo.services.assembly.description'),
        ]);

        $option = ServiceOption::create([
            'name'          => trans('offline.mall::demo.services.assembly.preassemble_name'),
            'description'   => trans('offline.mall::demo.services.assembly.preassemble_description'),
            'service_id'  => $assembly->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 99]));

        // Assign to Products
        Product::where('name', 'LIKE', 'Cruiser%')->get()->each(function (Product $product) use ($warranty, $assembly) {
            $product->services()->attach([$warranty->id, $assembly->id]);
        });
    }
}
