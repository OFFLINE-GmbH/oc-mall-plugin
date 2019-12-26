<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;

abstract class SortOrder
{
    /**
     * The currently active currency.
     * This is needed to sort items by price.
     * @var Currency
     */
    public $currency;
    /**
     * The default currency.
     * @var Currency
     */
    public $defaultCurrency;
    /**
     * Any active search filters.
     * @var Collection
     */
    protected $filters;

    public function __construct()
    {
        $this->currency        = Currency::activeCurrency();
        $this->defaultCurrency = Currency::defaultCurrency();
    }

    /**
     * Get a SortOrder instance from $key.
     *
     * @param string $key
     *
     * @return SortOrder
     */
    public static function fromKey(string $key): SortOrder
    {
        $options = self::options();
        if ( ! array_key_exists($key, $options)) {
            return $options[self::default()];
        }

        return $options[$key];
    }

    /**
     * The default sort order.
     *
     * @return string
     */
    public static function default()
    {
        return 'bestseller';
    }

    /**
     * These are all available options. Internal options
     * are not meant to be used by a customer.
     *
     * @param bool $excludeInternal
     *
     * @return array
     */
    public static function options($excludeInternal = false)
    {
        $options = [
            'manual'     => new Manual(),
            'bestseller' => new Bestseller(),
            'ratings'    => new Rating(),
            'latest'     => new Latest(),
            'price_low'  => new PriceLow(),
            'price_high' => new PriceHigh(),
            'oldest'     => new Oldest(),
            'random'     => new Random(),
            'name'       => new Name(),
        ];

        if ($excludeInternal) {
            unset($options['manual'], $options['random'], $options['name']);
        }

        return $options;
    }

    /**
     * These are all options as a key => label array.
     * This can be useful to populate a dropdown field.
     *
     * @return array
     */
    public static function dropdownOptions()
    {
        return [
            'bestseller' => (new Bestseller())->label(),
            'ratings'    => (new Rating())->label(),
            'manual'     => (new Manual())->label(),
            'latest'     => (new Latest())->label(),
            'price_low'  => (new PriceLow())->label(),
            'price_high' => (new PriceHigh())->label(),
            'oldest'     => (new Oldest())->label(),
            'random'     => (new Random())->label(),
            'name'       => (new Name())->label(),
        ];
    }

    /**
     * The translated label of this option.
     *
     * @return string
     */
    public function label(): string
    {
        return trans('offline.mall::lang.components.productsFilter.sortOrder.' . camel_case($this->key()));
    }

    /**
     * If a callable is returned from this method it will be
     * used as sort function.
     *
     * @param string $property
     * @param string $direction
     *
     * @return callable|bool
     *
     * @example return function($a, $b) use ($property, $direction) {
     *     return $a[$property] <=> $b[$property];
     * };
     */
    public function customSortFunction(string $property = '', string $direction = ''): ?callable
    {
        return null;
    }

    /**
     * Set any active search filters.
     *
     * @param Collection $filters
     *
     * @return SortOrder
     */
    public function setFilters(Collection $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Return the property name to sort by.
     * Nested properties are allowed (ex. price.USD)
     *
     * @return string
     */
    abstract public function property(): string;

    /**
     * Return the sort direction to use.
     * Possible values are ASC and DESC.
     *
     * @return string
     */
    abstract public function direction(): string;

    /**
     * Return a unique key for a sort method.
     *
     * @return string
     */
    abstract public function key(): string;
}
