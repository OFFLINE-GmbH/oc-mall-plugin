<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;

class Jersey extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => null,
            'user_defined_id'              => 'SHIRT002',
            'slug'                         => 'stormrider-jersey',
            'name'                         => trans('offline.mall::demo.products.jersey.name'),
            'description'                  => trans('offline.mall::demo.products.jersey.description'),
            'description_short'            => trans('offline.mall::demo.products.jersey.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.jersey.meta_title'),
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
            'material'  => trans('offline.mall::demo.products.properties.polyester'),
            'gender'    => trans('offline.mall::demo.products.properties.male'),
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
                'name'         => trans('offline.mall::demo.products.jersey.variants.brg_xs_name'),
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'XS',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.brg'),
                        'hex' => '#413f40',
                    ],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.brg_s_name'),
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'S',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.brg'),
                        'hex' => '#413f40',
                    ],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.brg_m_name'),
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'M',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.brg'),
                        'hex' => '#413f40',
                    ],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.brg_l_name'),
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'L',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.brg'),
                        'hex' => '#413f40',
                    ],
                ],
                'image_set_id' => $this->imageSets[0]->id,
            ],

            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.bbw_xs_name'),
                'stock'        => 5,
                'properties'   => [
                    'size'  => 'XS',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.bbw'),
                        'hex' => '#09d2bf',
                    ],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.bbw_s_name'),
                'stock'        => -2,
                'properties'   => [
                    'size'  => 'S',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.bbw'),
                        'hex' => '#09d2bf',
                    ],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.bbw_m_name'),
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'M',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.bbw'),
                        'hex' => '#09d2bf',
                    ],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.bbw_l_name'),
                'stock'        => 3,
                'properties'   => [
                    'size'  => 'L',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.bbw'),
                        'hex' => '#09d2bf',
                    ],
                ],
                'image_set_id' => $this->imageSets[1]->id,
            ],
            [
                'name'         => trans('offline.mall::demo.products.jersey.variants.bbw_xl_name'),
                'stock'        => 8,
                'properties'   => [
                    'size'  => 'XL',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.bbw'),
                        'hex' => '#09d2bf',
                    ],
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
                'name'        => trans('offline.mall::demo.products.images.jersey_red'),
                'is_main_set' => true,
                'images'      => [
                    realpath(__DIR__ . '/images/jersey-red-1.jpg'),
                ],
            ],
            [
                'name'        => trans('offline.mall::demo.products.images.jersey_blue'),
                'is_main_set' => false,
                'images'      => [
                    realpath(__DIR__ . '/images/jersey-blue-1.jpg'),
                    realpath(__DIR__ . '/images/jersey-blue-2.jpg'),
                ],
            ],
        ];
    }
}
