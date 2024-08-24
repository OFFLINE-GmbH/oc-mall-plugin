<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Demo;

use OFFLINE\Mall\Models\ProductPrice;

class GiftCard200 extends DemoProduct
{
    protected function attributes(): array
    {
        return [
            'brand_id'                     => null,
            'user_defined_id'              => 'GIFTCARD200',
            'slug'                         => 'gift-card-200',
            'name'                         => trans('offline.mall::demo.products.gift_card_200.name'),
            'description'                  => trans('offline.mall::demo.products.gift_card_200.description'),
            'description_short'            => trans('offline.mall::demo.products.gift_card_200.description_short'),
            'meta_title'                   => trans('offline.mall::demo.products.gift_card_200.meta_title'),
            'meta_keywords'                => 'gift, card',
            'weight'                       => 0,
            'inventory_management_method'  => 'product',
            'stock'                        => 200,
            'quantity_default'             => 1,
            'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => true,
            'links'                        => null,
            'stackable'                    => true,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'is_virtual'                   => true,
            'mpn'                          => 'GIFTCARD200',
            'published'                    => true,
        ];
    }

    protected function prices(): array
    {
        return [
            new ProductPrice(['currency_id' => 1, 'price' => 190.00]),
            new ProductPrice(['currency_id' => 2, 'price' => 200.00]),
            new ProductPrice(['currency_id' => 3, 'price' => 210.00]),
        ];
    }

    protected function categories(): array
    {
        return [
            $this->category('gift-cards')->id,
        ];
    }

    protected function images(): array
    {
        return [
            [
                'name'        => trans('offline.mall::demo.products.images.gift'),
                'is_main_set' => true,
                'images'      => [
                    realpath(__DIR__ . '/images/gift-card.jpg'),
                ],
            ],
        ];
    }

    protected function properties(): array
    {
        return [];
    }

    protected function variants(): array
    {
        return [];
    }

    protected function customFields(): array
    {
        return [];
    }

    protected function taxes(): array
    {
        return [];
    }
}
