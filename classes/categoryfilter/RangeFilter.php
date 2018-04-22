<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Property;

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
            $minValue = $this->minValue ? $this->minValue : 0;
            $maxValue = $this->maxValue ? $this->maxValue : PHP_INT_MAX;

            return (float)$item->filter_value >= (float)$minValue
                && (float)$item->filter_value <= (float)$maxValue;
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
