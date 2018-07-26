<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;

class CategoryTableSeeder extends Seeder
{
    public function run()
    {
        $category       = new Category();
        $category->name = 'Default category';
        $category->slug = 'default';
        $category->save();
    }
}