<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\ReviewCategory;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if ($useDemo) {
            $this->seedDemoData();
        } else {
            Category::create([
                'name'          => trans('offline.mall::demo.categories.example.name'),
                'slug'          => trans('offline.mall::demo.categories.example.slug'),
                'description'   => trans('offline.mall::demo.categories.example.description'),
            ]);
        }
    }

    /**
     * Seed demo data
     * @return void
     */
    protected function seedDemoData(): void
    {
        // Bike Category
        $bikes = Category::create([
            'name'             => trans('offline.mall::demo.categories.bikes.name'),
            'slug'             => 'bikes',
            'code'             => 'bikes',
            'sort_order'       => 0,
            'meta_title'       => trans('offline.mall::demo.categories.bikes.meta_title'),
            'meta_description' => trans('offline.mall::demo.categories.bikes.meta_description'),
        ]);
        $bikes->property_groups()->attach(
            PropertyGroup::where('slug', 'bike-specs')->first()->id,
            ['relation_sort_order' => 0]
        );
        $bikes->property_groups()->attach(
            PropertyGroup::where('slug', 'bike-size')->first()->id,
            ['relation_sort_order' => 1]
        );
        $bikes->property_groups()->attach(
            PropertyGroup::where('slug', 'suspension')->first()->id,
            ['relation_sort_order' => 2]
        );
        ReviewCategory::get()->each(function ($c) use ($bikes) {
            $bikes->review_categories()->attach($c->id);
        });

        // Sub-Categories
        Category::create([
            'name'                      => trans('offline.mall::demo.categories.mountainbikes.name'),
            'slug'                      => 'mountainbikes',
            'code'                      => 'mountainbikes',
            'sort_order'                => 0,
            'meta_title'                => trans('offline.mall::demo.categories.mountainbikes.meta_title'),
            'meta_description'          => trans('offline.mall::demo.categories.mountainbikes.meta_description'),
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
            'parent_id'                 => $bikes->id,
        ]);
        Category::create([
            'name'                      => trans('offline.mall::demo.categories.citybikes.name'),
            'slug'                      => 'citybikes',
            'code'                      => 'citybikes',
            'sort_order'                => 1,
            'meta_title'                => trans('offline.mall::demo.categories.citybikes.meta_title'),
            'meta_description'          => trans('offline.mall::demo.categories.citybikes.meta_description'),
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
            'parent_id'                 => $bikes->id,
        ]);

        // Clothing Category
        $clothing = Category::create([
            'name'             => trans('offline.mall::demo.categories.clothing.name'),
            'slug'             => 'clothing',
            'code'             => 'clothing',
            'sort_order'       => 1,
            'meta_title'       => trans('offline.mall::demo.categories.clothing.meta_title'),
            'meta_description' => trans('offline.mall::demo.categories.clothing.meta_description'),
        ]);
        $clothing->property_groups()->attach(
            PropertyGroup::where('slug', 'size')->first()->id,
            ['relation_sort_order' => 0]
        );
        $clothing->property_groups()->attach(
            PropertyGroup::where('slug', 'specs')->first()->id,
            ['relation_sort_order' => 1]
        );

        // Gift Cards
        Category::create([
            'name'                      => trans('offline.mall::demo.categories.gift.name'),
            'slug'                      => 'gift-cards',
            'code'                      => 'gift-cards',
            'sort_order'                => 4,
            'meta_title'                => trans('offline.mall::demo.categories.gift.meta_title'),
            'meta_description'          => trans('offline.mall::demo.categories.gift.meta_description'),
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
        ]);
    }
}
