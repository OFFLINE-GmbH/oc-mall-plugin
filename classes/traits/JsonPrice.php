<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait JsonPrice
{
    public $currencies;
    public $activeCurrency;
    public $baseCurrency;
    /**
     * @var Money
     */
    protected $money;

    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $currencies           = Currency::orderBy('is_default', 'DESC')->get();
        $this->currencies     = $currencies->keyBy('code');
        $this->baseCurrency   = $currencies->first();
        $this->activeCurrency = Currency::activeCurrency();
        $this->money          = app(Money::class);
    }

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

        if ( ! is_array($value)) {
            return $this->attributes[$key] = null;
        }

        $this->attributes[$key] = $this->mapJsonPrice($value);
    }

    public function mapJsonPrice($value, $factor = 100)
    {
        return json_encode(array_map(function ($value) use ($factor) {
            return $this->isNullthy($value) ? null : (float)$value * $factor;
        }, $value));
    }

    public function getAttribute($attribute)
    {
        $format = ends_with($attribute, '_formatted');

        if ($format) {
            $attribute = str_replace('_formatted', '', $attribute);
        }

        if ($attribute === 'price' && method_exists($this, 'getUserSpecificPrice')) {
            $value = $this->getUserSpecificPrice();
        } else {
            $value = parent::getAttribute($attribute);
        }

        // If the model already implements an accessor we don't mess with the attribute.
        if (method_exists($this, sprintf('get%sAttribute', studly_case($attribute)))) {
            return $value;
        }

        if ($value === null || ! $this->isPriceColumn($attribute)) {
            return $value;
        }

        if (is_array($value)) {
            $value = array_filter($this->fillMissingCurrencies($value), function ($item) {
                return $item !== null;
            });
        }

        return $format ? $this->formatPrice($value) : $this->roundPrice($value);
    }

    public function price($currency = null)
    {
        if ($currency === null) {
            $currency = Currency::activeCurrency();
        }
        if (is_string($currency)) {
            $currency = Currency::whereCode($currency)->firstOrFail();
        }

        return new Price([
            'price'       => $this->price[$currency->code] ?? 0,
            'currency_id' => $currency->id,
        ]);
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
            return $this->money->format($price, $product, $currency);
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
        return $this->activeCurrency;
    }

    /**
     * Fill in missing currency prices using the default
     * currency rate defined in the backend settings.
     *
     * @param $value
     *
     * @return array
     */
    protected function fillMissingCurrencies($value): array
    {
        // We are in the backend editing the price information. In this case
        // we actually want missing currencies to be displayed as null values.
        if (session()->get('mall.variants.disable-inheritance')) {
            return $value;
        }

        $basePrice = $value[$this->baseCurrency['code']] ?? null;

        return collect($value)->map(function ($price, $currency) use ($basePrice) {
            if ($price !== null || $basePrice === null) {
                return $price;
            }

            return $basePrice * (float)$this->currencies[$currency]['rate'] ?? 1;
        })->toArray();
    }
}
