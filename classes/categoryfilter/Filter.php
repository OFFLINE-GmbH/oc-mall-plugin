<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

abstract class Filter
{
    public static $specialProperties = [
        'price'       => RangeFilter::class,
        'category_id' => SetFilter::class,
        'brand'       => SetFilter::class,
        'on_sale'     => SetFilter::class,
    ];

    public $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public static function isSpecialProperty(string $id): bool
    {
        return array_key_exists($id, self::$specialProperties);
    }

    abstract public function values(): array;
}
