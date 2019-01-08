<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class Random extends SortOrder
{
    public function key(): string
    {
        return 'random';
    }

    public function property(): string
    {
        return '_';
    }

    public function direction(): string
    {
        return '_';
    }

    /**
     * Sort all values by a random number.
     *
     * @param string $property
     * @param string $direction
     *
     * @return callable|null
     */
    public function customSortFunction(string $property = '', string $direction = ''): ?callable
    {
        return function($a, $b) {
            return random_int(-1, 1);
        };
    }
}
