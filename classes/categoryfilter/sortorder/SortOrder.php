<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;


use OFFLINE\Mall\Models\Currency;

abstract class SortOrder
{
    public $currency;

    public function __construct()
    {
        $this->currency = Currency::activeCurrency();
    }

    public static function fromKey(string $key): SortOrder
    {
        $options = self::options();
        if ( ! array_key_exists($key, $options)) {
            return $options[self::default()];
        }

        return $options[$key];
    }

    public static function default()
    {
        return 'bestseller';
    }

    public static function options()
    {
        return [
            'bestseller' => new Bestseller(),
            'latest'     => new Latest(),
            'price_low'  => new PriceLow(),
            'price_high' => new PriceHigh(),
            'oldest'     => new Oldest(),
        ];
    }

    public function label(): string
    {
        return trans('offline.mall::lang.components.categoryFilter.sortOrder.' . camel_case($this->key()));
    }

    abstract public function property(): string;

    abstract public function order(): string;

    abstract public function key(): string;
}