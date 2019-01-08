<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

abstract class Price extends SortOrder
{
    /**
     * Calculate missing currency values on-the-fly.
     *
     * @param string $property
     * @param string $direction
     *
     * @return callable|null
     */
    public function customSortFunction(string $property = '', string $direction = ''): ?callable
    {
        return function ($a, $b) use ($property, $direction) {
            $priceA = array_get($a, $property);
            $priceB = array_get($b, $property);

            if ($priceA === null) {
                $base = $this->getBasePrice($a);
                $priceA = $this->calculatePrice($base);
            }
            if ($priceB === null) {
                $base = $this->getBasePrice($b);
                $priceB = $this->calculatePrice($base);
            }

            return $direction === 'asc' ? $priceA <=> $priceB : $priceB <=> $priceA;
        };
    }

    protected function calculatePrice($price): int
    {
        return $price * $this->currency->rate;
    }

    protected function getBasePrice($record)
    {
        return array_get(
            $record, 'prices.' . $this->defaultCurrency->code,
            array_get($record, 'parent_prices.' . $this->defaultCurrency->code)
        );
    }

    public function property(): string
    {
        return 'prices.' . $this->currency->code;
    }
}
