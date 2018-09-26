<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;

class ShippingMethodTableSeeder extends Seeder
{
    public function run()
    {
        $method             = new ShippingMethod();
        $method->name       = 'Default';
        $method->sort_order = 1;
        $method->save();

        (new Price([
            'price'          => 20,
            'currency_id'    => 1,
            'priceable_type' => ShippingMethod::MORPH_KEY,
            'priceable_id'   => $method->id,
        ]))->save();

        (new Price([
            'price'          => 30,
            'currency_id'    => 2,
            'priceable_type' => ShippingMethod::MORPH_KEY,
            'priceable_id'   => $method->id,
        ]))->save();
    }
}
