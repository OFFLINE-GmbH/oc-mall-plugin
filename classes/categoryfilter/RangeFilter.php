<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;


use Illuminate\Support\Collection;

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

    public function apply(Collection $items): Collection
    {
        return $this->setFilterValues($items)->filter(function ($item) {
            return $item->filter_value >= $this->minValue
                && $item->filter_value <= $this->maxValue;
        });
    }
}