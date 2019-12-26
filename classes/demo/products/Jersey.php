<?php

namespace OFFLINE\Mall\Classes\Demo\Products;

use OFFLINE\Mall\Models\ProductPrice;

class Jersey extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => null,
            'user_defined_id'              => 'SHIRT002',
            'name'                         => 'Stormrider Jersey Men',
            'slug'                         => 'stormrider-jersey',
            'description_short'            => 'Polyester',
            'description'                  => '<p>The fast-drying and breathable materials of the Stormrider Jersey ensure the perfect balance between durability, abrasion resistance and comfort. </p>',
            'meta_title'                   => 'Stormrider Jersey Men',
            'meta_keywords'                => 'jersey, mtb, stormrider',
            'weight'                       => 120,
            'inventory_management_method'  => 'variant',
            'quantity_default'             => 1,
            'quantity_max'                 => null,
            'allow_out_of_stock_purchases' => false,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => 'JERSEY',
            'group_by_property_id'         => $this->property('color')->id,
            'published'                    => true,
        ];
    }

    protected function taxes(): array
    {
        return [1];
    }

    protected function prices(): array
    {
        return [
            new ProductPrice(['currency_id' => 1, 'price' => 79.90]),
            new ProductPrice(['currency_id' => 2, 'price' => 69.90]),
            new ProductPrice(['currency_id' => 3, 'price' => 81.90]),
        ];
    }

    protected function properties(): array
    {
        return [
            'material' => 'Polyester',
            'gender'   => 'Male',
        ];
    }

    protected function categories(): array
    {
        return [
            $this->category('clothing')->id,
        ];
    }

    protected function variants(): array
    {
        return [
            [
                'name'         => 'Stormrider Jersey Men black/red/gray XS',
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'XS',
                    'color' => ['name' => 'black/red/gray', 'hex' => '#413f40'],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/red/gray S',
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'S',
                    'color' => ['name' => 'black/red/gray', 'hex' => '#413f40'],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/red/gray M',
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'M',
                    'color' => ['name' => 'black/red/gray', 'hex' => '#413f40'],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/red/gray L',
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'L',
                    'color' => ['name' => 'black/red/gray', 'hex' => '#413f40'],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],


            [
                'name'         => 'Stormrider Jersey Men black/blue/white XS',
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'XS',
                    'color' => ['name' => 'black/blue/white', 'hex' => '#09d2bf'],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/blue/white S',
                'stock'        => -2,
                'properties'   => [
                    'size'  => 'S',
                    'color' => ['name' => 'black/blue/white', 'hex' => '#09d2bf'],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/blue/white M',
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'M',
                    'color' => ['name' => 'black/blue/white', 'hex' => '#09d2bf'],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/blue/white L',
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'L',
                    'color' => ['name' => 'black/blue/white', 'hex' => '#09d2bf'],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => 'Stormrider Jersey Men black/blue/white XL',
                'stock'        => 8,
                'properties'   => [
                    'size'  => 'XL',
                    'color' => ['name' => 'black/blue/white', 'hex' => '#09d2bf'],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
        ];
    }

    protected function customFields(): array
    {
        return [

        ];
    }

    protected function images(): array
    {
        return [
            [
                'name'        => 'Jersey red',
                'is_main_set' => true,
                'images'      => [
                    realpath(__DIR__ . '/images/jersey-red-1.jpg'),
                ],
            ],
            [
                'name'        => 'Jersey blue',
                'is_main_set' => false,
                'images'      => [
                    realpath(__DIR__ . '/images/jersey-blue-1.jpg'),
                    realpath(__DIR__ . '/images/jersey-blue-2.jpg'),
                ],
            ],
        ];
    }
}
