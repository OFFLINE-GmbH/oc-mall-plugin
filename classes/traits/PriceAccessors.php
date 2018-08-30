<?php


namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\Currency;

trait PriceAccessors
{
    protected function price($currency = null, $relation = 'prices')
    {
        if ($currency === null) {
            $currency = Currency::activeCurrency()->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        return optional($this->$relation->where('currency_id', $currency)->first());
    }

    public function priceInCurrency($currency = null, $relation = 'prices')
    {
        return $this->price($currency, $relation)->decimal;
    }

    public function priceInCurrencyFormatted($currency = null, $relation = 'prices')
    {
        $price = $this->priceInCurrencyInteger($currency, $relation);

        return format_money($price, $this);
    }

    public function priceInCurrencyInteger($currency = null, $relation = 'prices')
    {
        return $this->price($currency, $relation)->integer;
    }
}