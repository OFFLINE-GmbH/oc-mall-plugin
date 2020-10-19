<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Currency;

trait Rounding
{
    /**
     * @var Currency
     */
    private $currency;

    protected function getCurrency(): Currency
    {
        if ($this->currency) {
            return $this->currency;
        }

        return $this->currency = Currency::activeCurrency();
    }

    protected function round($int, ?int $factor = null)
    {
        if ($factor === null) {
            $factor = $this->getCurrency()->rounding;
        }

        if ( ! $factor) {
            return $int;
        }

        $factor = 1 / $factor;

        return round($int * $factor) / $factor;
    }
}