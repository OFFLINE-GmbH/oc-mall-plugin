<?php

namespace OFFLINE\Mall\Classes\Utils;

use Illuminate\Support\Facades\App;
use OFFLINE\Mall\Models\Currency;

class DefaultMoney implements Money
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct()
    {
        $this->twig = App::make('mall.twig.environment');
    }

    public function format(?int $value, $product = null, ?Currency $currency = null): string
    {
        $currency = $currency ?? Currency::activeCurrency();

        $value    = app(Money::class)->round($value, $currency['decimals']);
        $integers = floor($value);
        $decimals = round(($value - $integers) * 100, 0);

        return $this->render($currency['format'], [
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

    protected function render($contents, array $vars)
    {
        $template = $this->twig->createTemplate($contents);

        return $template->render($vars);
    }
}
