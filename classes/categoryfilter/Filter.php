<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Property;

abstract class Filter
{
    public $property;
    public static $specialProperties = ['price'];

    public function __construct($property)
    {
        $this->property = $property;
    }

    abstract public function apply(Collection $items): Collection;

    abstract public function getValues(): array;

    public function setFilterValues(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item->setAttribute('filter_value', $this->getFilterValue($item));

            return $item;
        })->reject(function ($item) {
            return $item->filter_value === null;
        });
    }

    public function getFilterValue($item)
    {
        if ($this->isSpecialProperty()) {
            if ($this->property === 'price') {
                return $item->priceInCurrency();
            }

            return $item->getAttribute($this->property);
        }

        $value = $item->property_values->where('property_id', $this->property->id)->first();
        if ($value === null) {
            // The filtered property is specified on the product, not on the variant
            $value = $item->product->property_values->where('property_id', $this->property->id)->first();
        }

        $value = $value ? $value->value : null;

        return \is_array($value) ? json_encode($value) : $value;
    }

    protected function isSpecialProperty(): bool
    {
        return ! $this->property instanceof Property
            && \in_array($this->property, self::$specialProperties, true);
    }
}
