<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Product;

trait Price
{
    public function getPriceColumns(): array
    {
        return ['price'];
    }

    public function setAttribute($key, $value)
    {
        if ($value === null) {
            return $this->attributes[$key] = null;
        }

        if ( ! in_array($key, $this->getPriceColumns())) {
            return parent::setAttribute($key, $value);
        }
        $this->attributes[$key] = (float)$value * 100;
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);
        if ( ! in_array($key, $this->getPriceColumns())) {
            return $value;
        }

        if ($value === null) {
            return $value;
        }

        return $this->formatPrice((int)$value);
    }

    public function formatPrice($price)
    {
        return format_money($price, $this instanceof Product ? $this : null);
    }
}
