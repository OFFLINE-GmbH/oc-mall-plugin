<?php

namespace OFFLINE\Mall\Classes\Demo\Products;

use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Review;

class Cruiser1500 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => $this->brand('cruiser-bikes')->id,
            'user_defined_id'              => 'CITY002',
            'name'                         => 'Cruiser 1500',
            'slug'                         => 'cruiser-1500',
            'description_short'            => 'Think pink',
            'description'                  => '<p>Find your passion for city biking on Cruisers’ Model 1500. Whether you want to ride to the train or commute to work this is the right bike for you. The aluminium frame is feather light and durable.</p>',
            'meta_title'                   => 'Cruiser 1500 Citybike',
            'meta_description'             => 'Find your passion for city biking on Cruisers’ Model 1500',
            'meta_keywords'                => 'city, citybike, curiser, bike',
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
            'color'       => ['name' => 'Think pink', 'hex' => '#f686aa'],
            'rear-travel' => '0',
            'fork-travel' => '0',
            'material'    => 'Aluminium',
            'gender'      => 'Female',
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
                'name'        => 'Main images',
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
                    'title'       => 'This bike is for girls',
                    'description' => "So I've bought this bike last week and now my friend pointed out, that 
                    it is actually a girl's bike. This should be mentioned in the description!",
                    'rating'      => 1,
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
