<?php

namespace OFFLINE\Mall\Classes\Traits;

trait Price
{
    public function getPriceColumns(): array
    {
        return ['price'];
    }

    public function setAttribute($key, $value)
    {
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

        return $this->formatPrice((int)$value / 100);
    }

    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }
}
