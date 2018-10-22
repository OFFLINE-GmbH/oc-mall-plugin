<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Tax;

class TaxTableSeeder extends Seeder
{
    public function run()
    {
        $method             = new Tax();
        $method->name       = 'Default';
        $method->percentage = 8;
        $method->save();
    }
}
