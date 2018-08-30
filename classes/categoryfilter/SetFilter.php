<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use October\Rain\Database\QueryBuilder;

class SetFilter extends Filter
{
    public $values;

    public function __construct($property, array $values)
    {
        parent::__construct($property);
        $this->values = $values;
    }

    public function apply(QueryBuilder $query, $index): QueryBuilder
    {
        $alias = $this->applyJoin($query, $index);

        return $query->where(function ($query) use ($alias) {
            $query->where("${alias}.property_id", $this->property->id)
                  ->whereIn("${alias}.value", $this->getValues());
        });
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
