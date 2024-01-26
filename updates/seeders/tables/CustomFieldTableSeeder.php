<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Product;

class CustomFieldTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if (app()->environment() != 'testing') {
            return;
        }
        $field = new CustomField();
        $field->name = 'Test';
        $field->type = 'dropdown';
        $field->save();

        $option = new CustomFieldOption();
        $option->name = 'Test';
        $option->option_value = 'test';
        $option->sort_order = 1;
        $field->custom_field_options()->save($option);

        $option = new CustomFieldOption();
        $option->name = 'Test2';
        $option->option_value = 'test2';
        $option->sort_order = 2;
        $field->custom_field_options()->save($option);
        $field->products()->attach(Product::first()->id);
    }
}
