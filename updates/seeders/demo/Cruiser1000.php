<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Review;

class Cruiser1000 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => $this->brand('cruiser-bikes')->id,
            'user_defined_id'              => 'CITY001',
            'slug'                         => 'cruiser-1000',
            'name'                         => 'Cruiser 1000',
            'description'                  => trans('offline.mall::demo.products.cruiser_1000.description'),
            'description_short'            => trans('offline.mall::demo.products.cruiser_1000.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.cruiser_1000.meta_title'),
            'meta_description'             => trans('offline.mall::demo.products.cruiser_1000.meta_description'),
            'meta_keywords'                => 'city, citybike, cruiser, bike',
            'weight'                       => 9000,
            'inventory_management_method'  => 'variant',
            'quantity_default'             => 1,
            'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => false,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => 'CRUISER1000',
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
                'name'  =>  trans('offline.mall::demo.products.properties.dark_grey'),
                'hex'   => '#333333',
            ],
            'rear-travel' => '0',
            'fork-travel' => '110',
            'material'    => trans('offline.mall::demo.products.properties.aluminium'),
            'gender'      => trans('offline.mall::demo.products.properties.unisex'),
        ];
    }

    protected function categories(): array
    {
        return [
            $this->category('citybikes')->id,
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

    protected function additionalPrices(): array
    {
        return [
            new Price(['currency_id' => 1, 'price' => 999, 'price_category_id' => 1]),
            new Price(['currency_id' => 2, 'price' => 999, 'price_category_id' => 1]),
            new Price(['currency_id' => 3, 'price' => 999, 'price_category_id' => 1]),
        ];
    }

    protected function variants(): array
    {
        return [
            [
                'name'       => 'Cruiser 1000 27.5" S',
                'stock'      => 4,
                'prices'     => $this->prices(),
                'old_price'  => ['USD' => 1195, 'CHF' => 1599, 'EUR' => 1795],
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1000 27.5" M',
                'stock'      => 2,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1000 27.5" L',
                'stock'      => 0,
                'properties' => [
                    'frame-size' => 'L (48cm / 19")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 1000 29" S',
                'stock'      => 1,
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 1000 29" M',
                'stock'      => 8,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 1000 29" L',
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
            [
                'name'     =>  trans('offline.mall::demo.products.fields.include_bike_assembly'),
                'type'     => 'checkbox',
                'price'    => ['USD' => 490, 'EUR' => 200, 'CHF' => 5900],
                'required' => false,
            ],
        ];
    }

    protected function images(): array
    {
        return [
            [
                'name'          =>  trans('offline.mall::demo.products.images.main'),
                'is_main_set'   => true,
                'images'        => [
                    realpath(__DIR__ . '/images/cruiser-1000-1.jpg'),
                    realpath(__DIR__ . '/images/cruiser-1000-2.jpg'),
                ],
            ],
        ];
    }

    protected function reviews(): array
    {
        return [
            [
                'review'           => new Review([
                    'title'       => trans('offline.mall::demo.products.cruiser_1000.reviews.great_bike.title'),
                    'description' => trans('offline.mall::demo.products.cruiser_1000.reviews.great_bike.description'),
                    'rating'      => 5,
                    'pros'        => [
                        ['value' => trans('offline.mall::demo.products.cruiser_1000.reviews.great_bike.pros_01')],
                        ['value' => trans('offline.mall::demo.products.cruiser_1000.reviews.great_bike.pros_02')],
                    ],
                    'cons'        => [
                        ['value' => trans('offline.mall::demo.products.cruiser_1000.reviews.great_bike.cons_01')],
                    ],
                ]),
                'category_reviews' => [
                    5,
                    5,
                    5,
                ],
            ],
            [
                'review'           => new Review([
                    'rating' => 5,
                ]),
                'category_reviews' => [
                    4,
                    4,
                    5,
                ],
            ],
        ];
    }
}
