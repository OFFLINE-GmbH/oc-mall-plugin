<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

class Manual extends SortOrder
{
    public function key(): string
    {
        return 'manual';
    }

    /**
     * The property for manual sort order is depending on
     * the category that is sorted.
     *
     * @return string
     */
    public function property(): string
    {
        $value = optional($this->filters->get('category_id'))->values()[0] ?? null;
        if ( ! $value) {
            return '_category_missing';
        }

        return 'sort_orders.' . $value;
    }

    public function direction(): string
    {
        return 'asc';
    }
}
