<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;

class CustomFieldTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $field = CustomField::create([
            'name' => 'Test',
            'type' => 'dropdown',
        ]);

        $option = CustomFieldOption::create([
            'name' => 'Test',
            'option_value' => 'test',
            'sort_order' => 1,
        ]);
        $field->custom_field_options()->save($option);

        $option = CustomFieldOption::create([
            'name' => 'Test2',
            'option_value' => 'test2',
            'sort_order' => 2,
        ]);
        $field->custom_field_options()->save($option);
    }
}
