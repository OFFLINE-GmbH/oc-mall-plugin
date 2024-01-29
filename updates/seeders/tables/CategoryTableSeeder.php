<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        Category::create([
            'name' => 'Example category',
            'slug' => 'example',
        ]);
    }
}
