<?php

use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\Product;

if ( ! function_exists('format_money')) {
    /**
     * Formats a price. Adds the currency if provided.
     *
     * @param int          $value
     * @param Product|null $product
     * @param null         $currency
     *
     * @return string
     */
    function format_money(?int $value, Product $product = null, $currency = null)
    {
        $format   = CurrencySettings::activeCurrencyFormat();
        $value    = round($value / 100, 2);
        $integers = floor($value);
        $decimals = ($value - $integers) * 100;

        return Twig::parse($format, [
            'price'    => $value,
            'integers' => $integers,
            'decimals' => str_pad($decimals, 2, '0', STR_PAD_LEFT),
            'product'  => $product,
            'currency' => $currency ?: CurrencySettings::activeCurrency(),
        ]);
    }
}