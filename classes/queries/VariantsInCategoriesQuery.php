<?php

namespace OFFLINE\Mall\Classes\Queries;


use DB;
use Illuminate\Database\Query\JoinClause;
use October\Rain\Database\QueryBuilder;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

/**
 * This query is used to get all variants in one or more categories.
 */
class VariantsInCategoriesQuery
{
    /**
     * An array of category ids.
     * @var array<int>
     */
    protected $categories;
    protected $filters;

    public function __construct($categories, $filters)
    {
        $this->categories = $categories;
        $this->filters    = $filters;
    }

    public function query()
    {
        if ($this->filters->count() < 1) {
            return DB::table('offline_mall_product_variants as variants')
                     ->select('variants.id as id')
                     ->join('offline_mall_products as products', 'variants.product_id', '=', 'products.id')
                     ->where('products.published', true)
                     ->where('variants.published', true)
                     ->whereIn('products.category_id', $this->categories)
                     ->whereNull('variants.deleted_at')
                     ->whereNull('products.deleted_at');
        }

        return $this->withFilter();
    }

    protected function withFilter()
    {
        $variants = \DB::table('offline_mall_property_values as v1')
                       ->groupBy('v1.variant_id')
                       ->select(\DB::raw($this->coalesce()));

        $index = 1;
        foreach ($this->filters as $filter) {
            $filter->apply($variants, $index);
            $index++;
        }

        return $variants;
    }

    /**
     * Return the coalesce statement to query the first available variant_id.
     */
    protected function coalesce()
    {
        $parts = [];
        $count = $this->filters->count();
        for ($i = 1; $i <= $count; $i++) {
            $parts[] = "v${i}.variant_id";
        }

        return sprintf('COALESCE(%s) as variant_id, v1.product_id as product_id', implode(', ', $parts));
    }
}