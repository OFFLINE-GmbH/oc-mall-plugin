<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Database\Query\JoinClause;
use October\Rain\Database\QueryBuilder;
use OFFLINE\Mall\Models\Property;

abstract class Filter
{
    public $property;
    public static $specialProperties = ['price'];

    public function __construct($property)
    {
        $this->property = $property;
    }

    abstract public function apply(QueryBuilder $items, $index): QueryBuilder;

    abstract public function getValues(): array;

    protected function isSpecialProperty(): bool
    {
        return ! $this->property instanceof Property
            && \in_array($this->property, self::$specialProperties, true);
    }

    /**
     * Join the property_values table for each property to
     * enable a AND query over all available values.
     *
     * @param QueryBuilder $query
     * @param              $index
     *
     * @return string
     */
    protected function applyJoin(QueryBuilder $query, $index): string
    {
        $prevAlias = 'v' . ($index - 1);
        $alias     = 'v' . $index;

        if ($index > 1) {
            $query->join(
                "offline_mall_property_values as $alias",
                function (JoinClause $join) use ($alias, $prevAlias) {
                    $join->on("${prevAlias}.product_id", '=', "{$alias}.product_id")
                         ->where("${prevAlias}.variant_id", '=', \DB::raw("${alias}.variant_id"))
                         ->orWhereNull('v1.variant_id');
                }
            );
        }

        return $alias;
    }
}
