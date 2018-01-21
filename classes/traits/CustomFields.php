<?php

namespace OFFLINE\Mall\Classes\Traits;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\CustomFieldValue;

trait CustomFields
{
    /**
     * Returns the product's base price with all CustomFieldValue
     * prices added.
     *
     * @param CustomFieldValue[] $values
     *
     * @return int
     */
    public function priceIncludingCustomFieldValues(?Collection $values = null): int
    {
        $price = $this->price * 100;
        if ( ! $values || count($values) < 1) {
            return $price;
        }

        return $values->reduce(function ($total, CustomFieldValue $value) {
            return $total += $value->price * 100;
        }, $price);
    }
}
