<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;

class ShippingMethodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if ($useDemo) {
            return;
        }
        
        $method = ShippingMethod::create([
            'name'          => trans('offline.mall::demo.shipping_methods.standard'),
            'sort_order'    => 1,
            'is_default'    => true,
        ]);
        $method->prices()->saveMany([
            new Price([
                'price'          => 10,
                'currency_id'    => 1,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
            new Price([
                'price'          => 12,
                'currency_id'    => 2,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
            new Price([
                'price'          => 15,
                'currency_id'    => 3,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
        ]);
        
        $method = ShippingMethod::create([
            'name'                      => trans('offline.mall::demo.shipping_methods.express'),
            'sort_order'                => 1,
            'is_default'                => false,
            'guaranteed_delivery_days'  => 3,
        ]);
        $method->prices()->saveMany([
            new Price([
                'price'          => 20,
                'currency_id'    => 1,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
            new Price([
                'price'          => 24,
                'currency_id'    => 2,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
            new Price([
                'price'          => 30,
                'currency_id'    => 3,
                'priceable_type' => ShippingMethod::MORPH_KEY,
            ]),
        ]);
    }
}
