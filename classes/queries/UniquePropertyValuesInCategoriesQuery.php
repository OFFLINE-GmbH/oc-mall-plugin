<?php

namespace OFFLINE\Mall\Classes\Queries;

use DB;
use Illuminate\Support\Collection;
use October\Rain\Database\QueryBuilder;

/**
 * This query is used to get a list of all unique property values in one or
 * more categories. It is used to display a set of possible filters
 * for all available property values.
 */
class UniquePropertyValuesInCategoriesQuery
{
    /**
     * An array of category ids.
     * @var Collection
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
            ->selectRaw('
                MIN(offline_mall_property_values.id) AS id,
                offline_mall_property_values.value,
                offline_mall_property_values.index_value,
                offline_mall_property_values.property_id'
            )
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('offline_mall_products.published', true)
                      ->whereNull('offline_mall_product_variants.id');
                })->orWhere('offline_mall_product_variants.published', true);
            })
            ->whereIn('offline_mall_category_product.category_id', $this->categories->pluck('id'))
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->whereNull('offline_mall_products.deleted_at')
            ->where('offline_mall_property_values.value', '<>', '')
            ->whereNotNull('offline_mall_property_values.value')
            ->groupBy(
                'offline_mall_property_values.value',
                'offline_mall_property_values.index_value',
                'offline_mall_property_values.property_id'
            )
            ->leftJoin(
                'offline_mall_product_variants',
                'offline_mall_products.id',
                '=',
                'offline_mall_product_variants.product_id'
            )
            ->leftJoin(
                'offline_mall_category_product',
                'offline_mall_products.id',
                '=',
                'offline_mall_category_product.product_id'
            )
            ->join(
                'offline_mall_property_values',
                'offline_mall_products.id',
                '=',
                'offline_mall_property_values.product_id'
            );
    }
}
