<?php

use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\Product;

if ( ! function_exists('format_money')) {
    /**
     * Formats a price. Adds the currency if provided.
     *
     * @param int          $value
     * @param Product|null $product
     * @param null         $currencyCode
     *
     * @return string
     */
    function format_money(?int $value, $product = null, $currencyCode = null, $factor = 100)
    {
        $currency = $currencyCode ? CurrencySettings::currencyByCode($currencyCode) : CurrencySettings::activeCurrency();
        // Apply currency rate to price.
        $value *= (float)$currency['rate'];

        $value    = round($value / $factor, 2);
        $integers = floor($value);
        $decimals = ($value - $integers) * $factor;

        return Twig::parse(CurrencySettings::currencyFormatByCode($currency['code']), [
            'price'    => $value,
            'integers' => $integers,
            'decimals' => str_pad($decimals, 2, '0', STR_PAD_LEFT),
            'product'  => $product,
            'currency' => $currency,
        ]);
    }
}