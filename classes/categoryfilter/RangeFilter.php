<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use October\Rain\Database\QueryBuilder;

class RangeFilter extends Filter
{
    public $minValue;
    public $maxValue;

    public function __construct($property, $minValue, $maxValue)
    {
        parent::__construct($property);
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public function apply(QueryBuilder $query, $index): QueryBuilder
    {
        $alias = $this->applyJoin($query, $index);

        return $query->where(function ($query) use ($alias) {
            $query->where("${alias}.property_id", $this->property->id)
                  ->where(function ($query) use ($alias) {
                      $query->where("${alias}.value", '>=', $this->minValue)
                            ->where("${alias}.value", '<=', $this->maxValue);
                  });
        });
    }

    public function getValues(): array
    {
        return [
            'min' => $this->minValue,
            'max' => $this->maxValue,
        ];
    }
}
