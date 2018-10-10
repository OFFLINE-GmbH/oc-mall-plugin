<?php

namespace OFFLINE\Mall\Classes\Utils;

use October\Rain\Parse\Twig;
use OFFLINE\Mall\Models\Currency;

class DefaultMoney implements Money
{
    public function format(?int $value, $product = null, ?Currency $currency = null): string
    {
        $currency = $currency ?? Currency::activeCurrency();

        $value    = app(Money::class)->round($value, $currency['decimals']);
        $integers = floor($value);
        $decimals = ($value - $integers) * 100;

        return (new Twig)->parse($currency['format'], [
            'price'    => $value,
            'integers' => $integers,
            'decimals' => str_pad($decimals, 2, '0', STR_PAD_LEFT),
            'product'  => $product,
            'currency' => $currency,
        ]);
    }

    public function round($value, $decimals = 2): float
    {
        return round($value / 100, $decimals ?? 2);
    }
}
