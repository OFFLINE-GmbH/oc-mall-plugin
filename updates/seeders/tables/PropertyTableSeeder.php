<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;

class PropertyTableSeeder extends Seeder
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
            $this->seedCoreData();
        }
    }

    /**
     * Seed core data
     * @return void
     */
    protected function seedCoreData(): void
    {
        $category = Category::first();

        // Dimensions
        $propertyGroup = PropertyGroup::create([
            'name' => trans('offline.mall::demo.property_groups.dimensions'),
            'slug' => 'dimensions',
        ]);
        $propertyGroup->properties()->attach([
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.height'),
                'type' => 'text',
                'unit' => 'mm',
            ])->id,
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.width'),
                'type' => 'text',
                'unit' => 'mm',
            ])->id,
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.depth'),
                'type' => 'text',
                'unit' => 'mm',
            ])->id,
        ]);

        if (!empty($category)) {
            $category->property_groups()->attach($propertyGroup->id);
        }

        // Size
        $propertyGroup = PropertyGroup::create([
            'name' => trans('offline.mall::demo.property_groups.size'),
            'slug' => 'size',
        ]);
        $propertyGroup->properties()->attach(
            Property::create([
                'name'      => trans('offline.mall::demo.property_groups.size'),
                'type'      => 'dropdown',
                'unit'      => '',
                'slug'      => 'size',
                'options'   => [
                    ['value' => 'XS'],
                    ['value' => 'S'],
                    ['value' => 'M'],
                    ['value' => 'L'],
                    ['value' => 'XL'],
                ],
            ])->id,
            [
                'use_for_variants' => true,
                'filter_type' => 'set',
            ]
        );

        if (!empty($category)) {
            $category->property_groups()->attach($propertyGroup->id);
        }
    }

    /**
     * Seed demo data
     * @return void
     */
    protected function seedDemoData(): void
    {
        // Bike Specifications
        $specsGroup = PropertyGroup::create([
            'name'         => trans('offline.mall::demo.property_groups.bike_specs'),
            'display_name' => trans('offline.mall::demo.property_groups.specs'),
            'slug'         => 'bike-specs',
        ]);
        $specsGroup->properties()->attach([
            Property::create([
                'name'    => trans('offline.mall::demo.property_groups.gender'),
                'type'    => 'dropdown',
                'unit'    => '',
                'slug'    => 'gender',
                'options' => [
                    ['value' => trans('offline.mall::demo.property_groups.male')],
                    ['value' => trans('offline.mall::demo.property_groups.female')],
                    ['value' => trans('offline.mall::demo.property_groups.unisex')],
                ],
            ])->id,
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.material'),
                'type' => 'text',
                'unit' => '',
                'slug' => 'material',
            ])->id,
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.color'),
                'type' => 'color',
                'unit' => '',
                'slug' => 'color',
            ])->id,
        ], ['filter_type' => 'set']);

        // Bike Sizes
        $sizeGroup = PropertyGroup::create([
            'name' => trans('offline.mall::demo.property_groups.bike_size'),
            'slug' => 'bike-size',
        ]);
        $sizeGroup->properties()->attach([
            Property::create([
                'name'      => trans('offline.mall::demo.property_groups.frame_size'),
                'type'      => 'dropdown',
                'unit'      => 'cm/inch',
                'slug'      => 'frame-size',
                'options'   => [
                    ['value' => 'S (38cm / 15")'],
                    ['value' => 'M (43cm / 17")'],
                    ['value' => 'L (48cm / 19")'],
                    ['value' => 'XL (52cm / 20.5")'],
                ],
            ])->id,
            Property::create([
                'name'      => trans('offline.mall::demo.property_groups.wheel_size'),
                'type'      => 'dropdown',
                'unit'      => 'inch',
                'slug'      => 'wheel-size',
                'options'   => [
                    ['value' => '26"'],
                    ['value' => '27.5"'],
                    ['value' => '29"'],
                ],
            ])->id,
        ], [
            'use_for_variants'  => true,
            'filter_type'       => 'set',
        ]);

        // Bike Suspension
        $suspensionGroup = PropertyGroup::create([
            'name' => trans('offline.mall::demo.property_groups.suspension'),
            'slug' => 'suspension',
        ]);
        $suspensionGroup->properties()->attach([
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.fork_travel'),
                'type' => 'integer',
                'unit' => 'mm',
                'slug' => 'fork-travel',
            ])->id,
            Property::create([
                'name' => trans('offline.mall::demo.property_groups.rear_travel'),
                'type' => 'integer',
                'unit' => 'mm',
                'slug' => 'rear-travel',
            ])->id,
        ], ['filter_type' => 'range']);

        // Clothing
        $clothingGroup = PropertyGroup::create([
            'name'         => trans('offline.mall::demo.property_groups.clothing_specs'),
            'display_name' => trans('offline.mall::demo.property_groups.specs'),
            'slug'         => 'specs',
        ]);

        $clothingGroup->properties()->attach([
            Property::where('slug', 'color')->first()->id,
        ], [
            'use_for_variants'  => true,
            'filter_type'       => 'set',
        ]);
        
        $clothingGroup->properties()->attach([
            Property::where('slug', 'material')->first()->id,
            Property::where('slug', 'gender')->first()->id,
        ], ['filter_type'       => 'set']);
    }
}
