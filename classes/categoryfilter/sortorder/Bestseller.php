<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;


class Bestseller extends SortOrder
{
    public function key(): string
    {
        return 'bestseller';
    }

    public function property(): string
    {
        return 'sales_count';
    }

    public function order(): string
    {
        return 'desc';
    }
}