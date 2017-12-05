<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;

class ShippingMethodTableSeeder extends Seeder
{
    public function run()
    {
        $method        = new ShippingMethod();
        $method->name  = 'Default';
        $method->price = 20;
        $method->sort_order = 1;
        $method->save();
    }
}