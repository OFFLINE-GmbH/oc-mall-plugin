<?php


namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait PriceAccessors
{
    use NullPrice;

    /**
     * @var Money
     */
    protected $money;

    protected static function bootPriceAccessors()
    {
        static::extend(function ($model) {
            $model->money = app(Money::class);
        });
    }

    protected function priceRelation($currency = null, $relation = 'prices')
    {
        $currency = Currency::resolve($currency);

        if (method_exists($this, 'getUserSpecificPrice')) {
            if ($specific = $this->getUserSpecificPrice()) {
                return $specific->where('currency_id', $currency->id)->first()
                    ?? $this->nullPrice($currency, $specific);
            }
        }

        return $this->$relation->where('currency_id', $currency->id)->first()
            ?? $this->nullPrice($currency, $this->$relation);
    }

    public function price($currency = null, $relation = 'prices')
    {
        return $this->priceRelation($currency, $relation);
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

            return [$code => $this->money->format($price->integer, $product, $price->currency)];
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
