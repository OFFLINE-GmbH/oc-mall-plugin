<?php

namespace OFFLINE\Mall\Classes\Demo\Products;

use OFFLINE\Mall\Models\ProductPrice;

class RedShirt extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => null,
            'user_defined_id'              => 'SHIRT001',
            'name'                         => 'Red Shirt',
            'slug'                         => 'red-shirt',
            'description_short'            => 'Unisex',
            'description'                  => '<p>This is just a generic shirt. Brandless and cheap.</p>',
            'meta_title'                   => 'Red Shirt',
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
            'material' => 'Cotton',
            'gender'   => 'Unisex',
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
                'name'       => 'Red Shirt S',
                'stock'      => 2,
                'properties' => [
                    'size'  => 'S',
                    'color' => ['name' => 'Red', 'hex' => '#ff0000'],
                ],
            ],
            [
                'name'       => 'Red Shirt M',
                'stock'      => 200,
                'properties' => [
                    'size'  => 'M',
                    'color' => ['name' => 'Red', 'hex' => '#ff0000'],
                ],
            ],
            [
                'name'       => 'Red Shirt L',
                'stock'      => 0,
                'properties' => [
                    'size'  => 'L',
                    'color' => ['name' => 'Red', 'hex' => '#ff0000'],
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
                    realpath(__DIR__ . '/images/red-shirt.jpg'),
                ],
            ],
        ];
    }
}
