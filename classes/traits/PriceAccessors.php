<?php


namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

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
        if (is_string($currency)) {
            $currency = Currency::whereCode($currency)->firstOrFail()->id;
        }

        if (method_exists($this, 'getUserSpecificPrice')) {
            if ($specific = $this->getUserSpecificPrice()) {
                return $specific->where('currency_id', $currency)->first();
            }
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

    public function getPriceAttribute()
    {
        $this->prices->load('currency');

        return $this->mapCurrencyPrices($this->prices);
    }

    public function mapCurrencyPrices($items)
    {
        return $items->mapWithKeys(function ($price) {
            $code = $price->currency->code;

            $product = null;
            if ($this instanceof Variant) {
                $product = $this->product;
            }
            if ($this instanceof Product) {
                $product = $this;
            }

            return [$code => format_money($price->integer, $product, $price->currency)];
        });
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @internal
     *
     * @param $value
     */
    public function setPriceAttribute($value)
    {
        if ( ! is_array($value)) {
            return;
        }
        $this->updatePrices($value);
    }

    private function updatePrices($value, $field = null)
    {
        foreach ($value as $currency => $price) {
            Price::updateOrCreate([
                'priceable_id'   => $this->id,
                'priceable_type' => self::MORPH_KEY,
                'currency_id'    => Currency::where('code', $currency)->firstOrFail()->id,
                'field'          => $field,
            ], [
                'price' => $price,
            ]);
        }
    }
}