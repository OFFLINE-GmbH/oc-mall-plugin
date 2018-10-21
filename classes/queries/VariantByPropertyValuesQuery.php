<?php

namespace OFFLINE\Mall\Classes\Queries;

use DB;
use Illuminate\Support\Collection;
use October\Rain\Database\QueryBuilder;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\PropertyValue;

/**
 * This query is used to get a specific Variant model by
 * an array of PropertyValue ids.
 */
class VariantByPropertyValuesQuery
{
    /**
     * The Product the Variant belongs to.
     *
     * @var Product
     */
    protected $product;
    /**
     * ID's of PropertyValues.
     *
     * @var Collection
     */
    protected $ids;

    /**
     * VariantByPropertyValuesQuery constructor.
     *
     * @param Product $product
     * @param array   $ids
     */
    public function __construct(Product $product, Collection $ids)
    {
        $this->product = $product;
        $this->ids     = $ids;
    }

    /**
     * Return a query to get a single Variant by an array of PropertyValue ids.
     *
     * @return QueryBuilder
     */
    public function query()
    {
        $query = PropertyValue
            ::leftJoin(
                'offline_mall_product_variants',
                'variant_id',
                '=',
                'offline_mall_product_variants.id'
            )
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->where('offline_mall_product_variants.product_id', $this->product->id)
            ->select(DB::raw('variant_id, count(*) as matching_attributes'))
            ->groupBy(['variant_id'])
            ->with('variant')
            ->having('matching_attributes', count($this->ids))
            ->where($this->subQuery());

        return $query;
    }

    /**
     * Fetch all PropertyValues with matching property_id and value pairs.
     *
     * @return \Closure
     */
    protected function subQuery(): \Closure
    {
        return function ($query) {
            PropertyValue::whereIn('id', $this->ids)
                         ->get(['value', 'property_id'])
                         ->each(function (PropertyValue $propertyValue) use (&$query) {
                             $query->orWhereRaw(
                                 '(property_id, value) = (?, ?)',
                                 [$propertyValue->property_id, $propertyValue->safeValue]
                             );
                         });
        };
    }
}
