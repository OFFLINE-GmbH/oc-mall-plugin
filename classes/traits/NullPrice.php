<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;

trait NullPrice
{
    protected $defaultCurrency = null;

    protected function nullPrice($currency = null, $related)
    {
        $price   = null;
        $default = $this->getDefaultCurrency();

        if ( ! app()->runningInBackend()) {
            // Add missing prices only when running the frontend.
            $base = $related->where('currency_id', $default->id)->first();
            if ($base !== null) {
                $price = (int)($base->price * $currency->rate / 100);
            }
        }

        return new Price([
            'price'       => $price,
            'currency_id' => optional($currency)->id ?? Currency::activeCurrency()->id,
        ]);
    }

    protected function getDefaultCurrency()
    {
        if ( ! $this->defaultCurrency) {
            $this->defaultCurrency = Currency::defaultCurrency();
        }

        return $this->defaultCurrency;
    }
}
