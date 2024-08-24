<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Review;

class Cruiser1500 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => $this->brand('cruiser-bikes')->id,
            'user_defined_id'              => 'CITY002',
            'slug'                         => 'cruiser-1500',
            'name'                         => 'Cruiser 1500',
            'description'                  => trans('offline.mall::demo.products.cruiser_1500.description'),
            'description_short'            => trans('offline.mall::demo.products.cruiser_1500.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.cruiser_1500.meta_title'),
            'meta_description'             => trans('offline.mall::demo.products.cruiser_1500.meta_description'),
            'meta_keywords'                => 'city, citybike, cruiser, bike',
            'weight'                       => 7000,
            'inventory_management_method'  => 'variant',
            'quantity_default'             => 1,
            'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => false,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => 'CRUISER1500',
            'group_by_property_id'         => $this->property('wheel-size')->id,
            'published'                    => true,
        ];
    }

    protected function taxes(): array
    {
        return [1];
    }

    protected function properties(): array
    {
        return [
            'color'       => [
                'name' => trans('offline.mall::demo.products.properties.think_pink'),
                'hex' => '#f686aa',
            ],
            'rear-travel' => '0',
            'fork-travel' => '0',
            'material'    => trans('offline.mall::demo.products.properties.aluminium'),
            'gender'      => trans('offline.mall::demo.products.properties.female'),
        ];
    }

    protected function prices(): array
    {
        return [
            new ProductPrice(['currency_id' => 1, 'price' => 895]),
            new ProductPrice(['currency_id' => 2, 'price' => 795]),
            new ProductPrice(['currency_id' => 3, 'price' => 899]),
        ];
    }

    protected function categories(): array
    {
        return [
            $this->category('citybikes')->id,
        ];
    }

    protected function variants(): array
    {
        return [
            [
                'name'       => 'Cruiser 1500 27.5" S',
                'stock'      => 4,
                'prices'     => $this->prices(),
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1500 27.5" M',
                'stock'      => 2,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1500 27.5" L',
                'stock'      => 0,
                'properties' => [
                    'frame-size' => 'L (48cm / 19")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1500 29" S',
                'stock'      => 1,
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 1500 29" M',
                'stock'      => 8,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 1500 29" L',
                'stock'      => 5,
                'properties' => [
                    'frame-size' => 'L (48cm / 19")',
                    'wheel-size' => '29"',
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
                'name'        =>  trans('offline.mall::demo.products.images.main'),
                'is_main_set' => true,
                'images'      => [
                    realpath(__DIR__ . '/images/cruiser-1500-1.jpg'),
                ],
            ],
        ];
    }

    protected function reviews(): array
    {
        return [
            [
                'review'           => new Review([
                    'title'         => trans('offline.mall::demo.products.cruiser_1500.reviews.title'),
                    'description'   => trans('offline.mall::demo.products.cruiser_1500.reviews.description'),
                    'rating'        => 1,
                ]),
                'category_reviews' => [
                    1,
                    1,
                    1,
                ],
            ],
            [
                'review'           => new Review([
                    'rating' => 5,
                ]),
                'category_reviews' => [
                    3,
                    4,
                    5,
                ],
            ],
            [
                'review'           => new Review([
                    'rating' => 4,
                ]),
                'category_reviews' => [
                    5,
                    4,
                    5,
                ],
            ],
        ];
    }
}
