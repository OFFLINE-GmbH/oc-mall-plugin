<?php

namespace OFFLINE\Mall\Classes\Queries;

use DB;
use October\Rain\Database\QueryBuilder;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

/**
 * This query is used to get a list of all unique property values in one or
 * more categories. It is used to display a set of possible filters
 * for all available property values.
 */
class UniquePropertyValuesInCategoriesQuery
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

    /**
     * Return a query to get all unique product property values.
     *
     * @return QueryBuilder
     */
    public function query()
    {
        return DB
            ::table('offline_mall_products')
            ->selectRaw(\DB::raw('distinct
                offline_mall_property_values.value,
                offline_mall_property_values.index_value,
                offline_mall_property_values.property_id'
            ))
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('offline_mall_products.published', true)
                      ->whereNull('offline_mall_product_variants.id');
                })->orWhere('offline_mall_product_variants.published', true);
            })
            ->whereIn('offline_mall_products.category_id', $this->categories)
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->whereNull('offline_mall_products.deleted_at')
            ->leftJoin(
                'offline_mall_product_variants',
                'offline_mall_products.id',
                '=',
                'offline_mall_product_variants.product_id'
            )
            ->join(
                'offline_mall_property_values',
                'offline_mall_products.id',
                '=',
                'offline_mall_property_values.product_id'
            );
    }
}
