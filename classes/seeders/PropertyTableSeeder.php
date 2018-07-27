<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;

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
        $method->name = 'Depth';
        $method->type = 'text';
        $method->unit = 'mm';
        $method->save();

        $method          = new Property();
        $method->name    = 'Size';
        $method->type    = 'dropdown';
        $method->options = [
            ['value' => 'XS'],
            ['value' => 'S'],
            ['value' => 'M'],
            ['value' => 'L'],
            ['value' => 'XL'],
        ];
        $method->save();

        $propertyGroup = new PropertyGroup();
        $propertyGroup->name = 'Dimensions';
        $propertyGroup->save();
        $propertyGroup->properties()->attach([1, 2, 3]);

        $propertyGroup = new PropertyGroup();
        $propertyGroup->name = 'Size';
        $propertyGroup->save();
        $propertyGroup->properties()->attach([4]);

        $category = Category::first();
        $category->property_groups()->attach($propertyGroup->id);
    }
}
