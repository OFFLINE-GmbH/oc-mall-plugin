<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;

class CategoryTableSeeder extends Seeder
{
    public function run()
    {
        $method       = new Category();
        $method->name = 'Default category';
        $method->slug = 'default';
        $method->save();
    }
}
