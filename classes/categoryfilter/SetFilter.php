<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

class SetFilter extends Filter
{
    public $values;
    public bool $exclude = false;

    public function __construct($property, array $values, $exclude = false)
    {
        parent::__construct($property);
        $this->values = $values;
        $this->exclude = $exclude;
    }

    public function values(): array
    {
        return $this->values;
    }
}
