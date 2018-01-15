<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\CustomFieldValue;

trait CustomFields
{
    /**
     * Returns the product's base price with all CustomFieldValue
     * prices added.
     *
     * @param CustomFieldValue[] $value
     *
     * @return int
     */
    public function priceIncludingCustomFieldValues(array $value = []): int
    {
        $price = $this->getOriginal('price');
        if (count($value) < 1) {
            return $price;
        }

        return collect($value)->reduce(function ($total, CustomFieldValue $value) {
            if ( ! $value->custom_field_option) {
                return $total;
            }

            return $total += $value->custom_field_option->getOriginal('price');
        }, $price);
    }
}
