<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;

class RedShirt extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => null,
            'user_defined_id'              => 'SHIRT001',
            'slug'                         => 'red-shirt',
            'name'                         => trans('offline.mall::demo.products.red_shirt.name'),
            'description'                  => trans('offline.mall::demo.products.red_shirt.description'),
            'description_short'            => trans('offline.mall::demo.products.red_shirt.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.red_shirt.meta_title'),
            'meta_keywords'                => 'shirt, red',
            'weight'                       => 100,
            'inventory_management_method'  => 'variant',
            'quantity_default'             => 1,
            'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => false,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => 'REDSHIRT',
            'group_by_property_id'         => $this->property('size')->id,
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
            new ProductPrice(['currency_id' => 1, 'price' => 9.90]),
            new ProductPrice(['currency_id' => 2, 'price' => 11.90]),
            new ProductPrice(['currency_id' => 3, 'price' => 6.90]),
        ];
    }

    protected function properties(): array
    {
        return [
            'material'  => trans('offline.mall::demo.products.properties.cotton'),
            'gender'    => trans('offline.mall::demo.products.properties.unisex'),
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
                'name'       => trans('offline.mall::demo.products.red_shirt.variants.s_name'),
                'stock'      => 2,
                'properties' => [
                    'size'  => 'S',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.red'),
                        'hex' => '#ff0000',
                    ],
                ],
            ],
            [
                'name'       => trans('offline.mall::demo.products.red_shirt.variants.m_name'),
                'stock'      => 200,
                'properties' => [
                    'size'  => 'M',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.red'),
                        'hex' => '#ff0000',
                    ],
                ],
            ],
            [
                'name'       => trans('offline.mall::demo.products.red_shirt.variants.l_name'),
                'stock'      => 0,
                'properties' => [
                    'size'  => 'L',
                    'color' => [
                        'name' => trans('offline.mall::demo.products.properties.red'),
                        'hex' => '#ff0000',
                    ],
                ],
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
                'name'        => trans('offline.mall::demo.products.images.main'),
                'is_main_set' => true,
                'images'      => [
                    realpath(__DIR__ . '/images/red-shirt.jpg'),
                ],
            ],
        ];
    }
}
