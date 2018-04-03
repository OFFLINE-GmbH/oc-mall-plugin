<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Product;

class CustomFieldTableSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment() === 'testing') {
            $field       = new CustomField();
            $field->name = 'Test';
            $field->type = 'dropdown';
            $field->save();

            $option             = new CustomFieldOption();
            $option->name       = 'Test';
            $option->values     = 'test';
            $option->sort_order = 1;
            $field->custom_field_options()->save($option);

            $option             = new CustomFieldOption();
            $option->name       = 'Test2';
            $option->values     = 'test2';
            $option->sort_order = 2;
            $field->custom_field_options()->save($option);
            $field->products()->attach(Product::first()->id);
        }
    }
}
