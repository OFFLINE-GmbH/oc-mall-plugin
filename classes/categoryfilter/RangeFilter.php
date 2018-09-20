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

    public function values(): array
    {
        return [
            'min' => $this->minValue,
            'max' => $this->maxValue,
        ];
    }
}
