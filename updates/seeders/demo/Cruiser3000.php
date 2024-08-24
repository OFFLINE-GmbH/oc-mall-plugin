<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;

class Cruiser3000 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => $this->brand('cruiser-bikes')->id,
            'user_defined_id'              => 'MTB002',
            'slug'                         => 'cruiser-3000',
            'name'                         => 'Cruiser 3000',
            'description'                  => trans('offline.mall::demo.products.cruiser_3000.description'),
            'description_short'            => trans('offline.mall::demo.products.cruiser_3000.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.cruiser_3000.meta_title'),
            'meta_description'             => trans('offline.mall::demo.products.cruiser_3000.meta_description'),
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
            'mpn'                          => 'CRUISER3000',
            'group_by_property_id'         => $this->property('wheel-size')->id,
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
            new ProductPrice(['currency_id' => 1, 'price' => 1995]),
            new ProductPrice(['currency_id' => 2, 'price' => 1495]),
            new ProductPrice(['currency_id' => 3, 'price' => 2199]),
        ];
    }

    protected function properties(): array
    {
        return [
            'color'       => [
                'name'  => trans('offline.mall::demo.products.properties.heavens_blue'),
                'hex'   => '#02bbe6',
            ],
            'rear-travel' => '0',
            'fork-travel' => '130',
            'material'    => trans('offline.mall::demo.products.properties.aluminium'),
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
                'name'       => 'Cruiser 3000 27.5" S',
                'stock'      => 4,
                'prices'     => $this->prices(),
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 3000 27.5" M',
                'stock'      => 2,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 3000 27.5" L',
                'stock'      => 0,
                'properties' => [
                    'frame-size' => 'L (48cm / 19")',
                    'wheel-size' => '27.5"',
                ],
            ],
            [
                'name'       => 'Cruiser 3000 29" S',
                'stock'      => 1,
                'properties' => [
                    'frame-size' => 'S (38cm / 15")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 3000 29" M',
                'stock'      => 8,
                'properties' => [
                    'frame-size' => 'M (43cm / 17")',
                    'wheel-size' => '29"',
                ],
            ],
            [
                'name'       => 'Cruiser 3000 29" L',
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
                    realpath(__DIR__ . '/images/cruiser-3000-1.jpg'),
                    realpath(__DIR__ . '/images/cruiser-5000-2.jpg'),
                ],
            ],
        ];
    }
}
