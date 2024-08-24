<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Review;

class Cruiser5000 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => $this->brand('cruiser-bikes')->id,
            'user_defined_id'              => 'MTB001',
            'slug'                         => 'cruiser-5000',
            'name'                         => 'Cruiser 5000',
            'description'                  => trans('offline.mall::demo.products.cruiser_5000.description'),
            'description_short'            => trans('offline.mall::demo.products.cruiser_5000.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.cruiser_5000.meta_title'),
            'meta_description'             => trans('offline.mall::demo.products.cruiser_5000.meta_description'),
            'meta_keywords'                => 'mtb, mountainbike, cruiser, bike',
            'weight'                       => 14000,
            'inventory_management_method'  => 'variant',
            'quantity_default'             => 1,
            'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => false,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => 'CRUISER5000',
            'group_by_property_id'         => $this->property('wheel-size')->id,
            'published'                    => true,
        ];
    }

    protected function prices(): array
    {
        return [
            new ProductPrice(['currency_id' => 1, 'price' => 1995]),
            new ProductPrice(['currency_id' => 2, 'price' => 1495]),
            new ProductPrice(['currency_id' => 3, 'price' => 2199]),
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
                'name'  => trans('offline.mall::demo.products.properties.devils_red'),
                'hex'   => '#e74c3c',
            ],
            'rear-travel' => '155',
            'fork-travel' => '160',
            'material'    => trans('offline.mall::demo.products.properties.carbon'),
            'gender'      => trans('offline.mall::demo.products.properties.unisex'),
        ];
    }

    protected function categories(): array
    {
        return [
            $this->category('mountainbikes')->id,
        ];
    }

    protected function variants(): array
    {
        return [
            [
                'name'       => 'Cruiser 5000 27.5" S',
                'stock'      => 4,
                'prices'     => $this->prices(),
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 5000 27.5" M',
                'stock'      => 2,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 5000 27.5" L',
                'stock'      => 0,
                'properties' => [
                    'frame-size' => 'L (48cm / 19")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 5000 29" S',
                'stock'      => 1,
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 5000 29" M',
                'stock'      => 8,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 5000 29" L',
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
                    realpath(__DIR__ . '/images/cruiser-5000-1.jpg'),
                    realpath(__DIR__ . '/images/cruiser-5000-2.jpg'),
                ],
            ],
        ];
    }

    protected function reviews(): array
    {
        return [
            [
                'review'           => new Review([
                    'rating' => 3,
                ]),
                'category_reviews' => [
                    3,
                    3,
                    2,
                ],
            ],
            [
                'review'           => new Review([
                    'title'       => trans('offline.mall::demo.products.cruiser_5000.reviews.title'),
                    'description' => trans('offline.mall::demo.products.cruiser_5000.reviews.description'),
                    'rating'      => 3,
                ]),
                'category_reviews' => [
                    4,
                    5,
                    1,
                ],
            ],
            [
                'review'           => new Review([
                    'rating' => 2,
                ]),
                'category_reviews' => [
                    2,
                    2,
                    5,
                ],
            ],
        ];
    }
}
