<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait Price
{
    public function getPriceColumns(): array
    {
        return ['price'];
    }

    public function setAttribute($key, $value)
    {
        if ($this->isNullthy($value)) {
            return $this->attributes[$key] = null;
        }

        if ( ! $this->isPriceColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        $this->attributes[$key] = json_encode(array_map(function ($value) {
            return $this->isNullthy($value) ? null : (float)$value * 100;
        }, $value));
    }

    public function getAttribute($attribute)
    {
        $format = ends_with($attribute, '_formatted');

        if ($format) {
            $attribute = str_replace('_formatted', '', $attribute);
        }

        $value = parent::getAttribute($attribute);

        // If the model already implements an accessor we don't mess with the attribute.
        if (method_exists($this, sprintf('get%sAttribute', studly_case($attribute)))) {
            return $value;
        }

        if ($value === null || ! $this->isPriceColumn($attribute)) {
            return $value;
        }

        return $format ? $this->formatPrice($value) : $this->roundPrice($value);
    }

    /**
     * Intercept calls to all {price}inCurrency methods.
     */
    public function __call($method, $parameters)
    {
        $transformers = [
            'Integer'             => function ($values, $currency) {
                return array_map(function ($value) {
                    return (int)$value * 100;
                }, $values);
            },
            'InCurrency'          => function ($value, $currency) {
                return $value;
            },
            'InCurrencyInteger'   => function ($value, $currency) {
                return $value === null ? null : (int)($value * 100);
            },
            'InCurrencyFormatted' => function ($value, $currency) {
                return format_money($value * 100, null, $currency);
            },
        ];

        foreach ($transformers as $suffix => $closure) {
            if (\in_array($method, $this->priceAccessorMethods($suffix), true)) {
                $attr     = snake_case(preg_replace('/In(teger|Currency).*$/', '', $method));
                $currency = $parameters[0] ?? $this->useCurrency();

                $value = $this->getAttribute($attr);

                if (\is_array($value)
                    && ( ! ends_with($method, 'Integer')
                        || ends_with($method, 'InCurrencyInteger'))) {
                    $value = $value[$currency] ?? 0;
                }

                return $closure($value, $currency);
            }
        }

        return parent::__call($method, $parameters);
    }

    protected function priceAccessorMethods(string $suffix): array
    {
        return collect($this->getPriceColumns())->map(function ($column) use ($suffix) {
            return camel_case($column . $suffix);
        })->toArray();
    }

    public function formatPrice(array $price): array
    {
        $product = null;
        if ($this instanceof Product) {
            $product = $this;
        }
        if ($this instanceof Variant) {
            $product = $this->product;
        }

        return collect($price)->map(function ($price, $currency) use ($product) {
            return format_money($price, $product, $currency);
        })->toArray();
    }

    protected function isPriceColumn($key): bool
    {
        return collect($this->getPriceColumns())->flatMap(function ($col) {
            return [$col, $col . '_formatted'];
        })->contains($key);
    }

    protected function roundPrice($value)
    {
        $round = function ($value) {
            return $this->isNullthy($value) ? null : round((int)$value / 100, 2);
        };

        if ( ! is_array($value)) {
            return $round($value);
        }

        return array_map(function ($value) use ($round) {
            return $round($value);
        }, $value);
    }

    protected function isNullthy($value): bool
    {
        return \in_array($value, [null, ''], true);
    }

    protected function useCurrency()
    {
        return CurrencySettings::activeCurrency()['code'];
    }
}
