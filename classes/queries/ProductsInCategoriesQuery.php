<?php

namespace OFFLINE\Mall\Classes\Queries;


use DB;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

/**
 * This query is used to get all products in one or more categories.
 */
class ProductsInCategoriesQuery
{
    /**
     * An array of category ids.
     * @var array<int>
     */
    protected $categories;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function query()
    {
        return DB
            ::table('offline_mall_products')
            ->select('offline_mall_products.id')
            ->distinct()
            ->where('offline_mall_products.published', true)
            ->whereIn('offline_mall_products.category_id', $this->categories)
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->whereNull('offline_mall_products.deleted_at')
            ->join(
                'offline_mall_product_variants',
                'offline_mall_products.id',
                '=',
                'offline_mall_product_variants.product_id'
            )
            // Join Variant Property Values
            ->join('offline_mall_property_values as variant_property_values', function ($join) {
                $join
                    ->on('offline_mall_product_variants.id', '=', 'variant_property_values.describable_id')
                    ->where('variant_property_values.describable_type', Variant::MORPH_KEY);
            })
            // Join Product Property Values
            ->join('offline_mall_property_values as product_property_values', function ($join) {
                $join
                    ->on('offline_mall_products.id', '=', 'product_property_values.describable_id')
                    ->where('product_property_values.describable_type', Product::MORPH_KEY);
            });
    }
}