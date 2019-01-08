<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class PriceHigh extends PriceLow
{
    public function key(): string
    {
        return 'price_high';
    }

    public function property(): string
    {
        return 'prices.' . $this->currency->code;
    }

    public function direction(): string
    {
        return 'desc';
    }
}
