<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class Name extends SortOrder
{
    public function key(): string
    {
        return 'name';
    }

    public function property(): string
    {
        return 'name';
    }

    public function direction(): string
    {
        return 'asc';
    }
}
