<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;

class PropertyTableSeeder extends Seeder
{
    public function run()
    {
        $method       = new Property();
        $method->name = 'Height';
        $method->type = 'text';
        $method->unit = 'mm';
        $method->save();

        $method       = new Property();
        $method->name = 'Width';
        $method->type = 'text';
        $method->unit = 'mm';
        $method->save();

        $method       = new Property();
        $method->name = 'Size';
        $method->type = 'dropdown';
        $method->options = [
            ['value' => 'XS'],
            ['value' => 'S'],
            ['value' => 'M'],
            ['value' => 'L'],
            ['value' => 'XL']
        ];
        $method->save();

        $category = Category::first();
        $category->properties()->attach([1, 2, 3]);
    }
}
