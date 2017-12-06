<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\Product;

class CustomFieldTableSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment() === 'testing') {
            $field             = new CustomField();
            $field->product_id = Product::first()->id;
            $field->name       = 'Test';
            $field->type       = 'dropdown';
            $field->options    = [['name' => 'Test'], ['name' => 'Test2']];
            $field->save();
        }
    }
}
