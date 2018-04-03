<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;

class SetFilter extends Filter
{
    public $values;

    public function __construct($property, array $values)
    {
        parent::__construct($property);
        $this->values = $values;
    }

    public function apply(Collection $items): Collection
    {
        return $this->setFilterValues($items)->filter(function ($item) {
            return \in_array($item->filter_value, $this->values);
        });
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
