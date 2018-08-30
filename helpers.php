<?php

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\Product;

if ( ! function_exists('round_money')) {
    /**
     * Rounds an amount to the specified decimals.
     */
    function round_money($value, $decimals = 2)
    {
        return round($value / 100, $decimals ?? 2);
    }
}

if ( ! function_exists('format_money')) {
    /**
     * Formats a price. Adds the currency if provided.
     *
     * @param int           $value
     * @param null|Product  $product
     * @param null|Currency $currency
     *
     * @return string
     */
    function format_money(?int $value, $product = null, ?Currency $currency = null)
    {
        $currency = $currency ?? Currency::activeCurrency();

        $value    = round_money($value, $currency['decimals']);
        $integers = floor($value);
        $decimals = ($value - $integers) * 100;

        return Twig::parse($currency['format'], [
            'price'    => $value,
            'integers' => $integers,
            'decimals' => str_pad($decimals, 2, '0', STR_PAD_LEFT),
            'product'  => $product,
            'currency' => $currency,
        ]);
    }
}