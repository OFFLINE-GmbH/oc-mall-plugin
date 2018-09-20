<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

abstract class Filter
{
    public static $specialProperties = ['price', 'category_id'];

    public $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    abstract public function values(): array;
}
