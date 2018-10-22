<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class PriceLow extends SortOrder
{
    public function key(): string
    {
        return 'price_low';
    }

    public function property(): string
    {
        return 'prices.' . $this->currency->code;
    }

    public function direction(): string
    {
        return 'asc';
    }
}
