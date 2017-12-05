<?php

namespace OFFLINE\Mall\Classes\Traits;

trait Price
{
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (float)$value * 100;
    }

    public function getPriceAttribute($value)
    {
        return $this->formatPrice((int)$value / 100);
    }

    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }
}