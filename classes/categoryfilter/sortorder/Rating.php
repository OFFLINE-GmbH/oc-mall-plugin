<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class Rating extends SortOrder
{
    public function key(): string
    {
        return 'ratings';
    }

    public function property(): string
    {
        return 'reviews_rating';
    }

    public function direction(): string
    {
        return 'desc';
    }
}
