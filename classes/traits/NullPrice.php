<?php

namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;

trait NullPrice
{
    protected function nullPrice($currency = null)
    {
        return new Price([
            'price'       => null,
            'currency_id' => optional($currency)->id ?? Currency::activeCurrency()->id,
        ]);
    }
}