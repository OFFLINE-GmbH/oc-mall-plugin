<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

class SetFilter extends Filter
{
    public $values;

    public function __construct($property, array $values)
    {
        parent::__construct($property);
        $this->values = $values;
    }

    public function values(): array
    {
        return $this->values;
    }
}
