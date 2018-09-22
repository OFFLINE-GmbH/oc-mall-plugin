<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

class RangeFilter extends Filter
{
    public $minValue;
    public $maxValue;

    public function __construct($property, array $values)
    {
        parent::__construct($property);
        $this->minValue = $values[0];
        $this->maxValue = $values[1];
    }

    public function values(): array
    {
        return [
            'min' => $this->minValue,
            'max' => $this->maxValue,
        ];
    }
}
