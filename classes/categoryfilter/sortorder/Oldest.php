<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;


class Oldest extends SortOrder
{
    public function key(): string
    {
        return 'oldest';
    }

    public function property(): string
    {
        return 'created_at';
    }

    public function order(): string
    {
        return 'asc';
    }
}