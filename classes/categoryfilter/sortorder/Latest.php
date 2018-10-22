<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class Latest extends SortOrder
{
    public function key(): string
    {
        return 'latest';
    }

    public function property(): string
    {
        return 'created_at';
    }

    public function direction(): string
    {
        return 'desc';
    }
}
