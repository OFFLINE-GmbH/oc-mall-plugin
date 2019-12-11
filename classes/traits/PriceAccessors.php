<?php


namespace OFFLINE\Mall\Classes\Traits;

use Closure;
use October\Rain\Database\Collection;
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
    /**
     * Set this to true to force the inheritance
     * of pricing information.
     *
     * @var bool
     * @see PriceAccessors::withForcedPriceInheritance
     */
    protected $forcePriceInheritance = false;

    protected static function bootPriceAccessors()
    {
        static::extend(function ($model) {
            $model->money = app(Money::class);
        });
    }

    protected function priceRelation(
        $currency = null,
        $relation = 'prices',
        ?Closure $filter = null
    ) {
        $currency = Currency::resolve($currency);

        $query = $this->withFilter($filter, $this->$relation->where('currency_id', $currency->id));

        $price = $query->first() ?? $this->nullPrice($currency, $this->$relation, $relation, $filter);

        // If a user specific price is available for this model use it instead.
        // The official price is passed along as the "official" property on the specific price model.
        if (method_exists($this, 'getUserSpecificPrice')) {
            if ($specificPrices = $this->getUserSpecificPrice($price)) {
                // If a Collection is returned, the price in the current currency has to be filtered out first.
                if ($specificPrices instanceof Collection) {
                    $query    = $this->withFilter($filter, $specificPrices->where('currency_id', $currency->id));
                    $specific = $query->first();
                } else {
                    $specific = $specificPrices;
                }

                $specific = $specific ?? $this->nullPrice($currency, $specificPrices, $relation, $filter);

                $specific->official = $price;

                return $specific;
            }
        }

        return $price;
    }

    public function price($currency = null, $relation = 'prices', ?Closure $filter = null)
    {
        return $this->priceRelation($currency, $relation, $filter);
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
     * Run the provided closure with forced price inheritance.
     *
     * @param Closure $fn
     *
     * @return mixed
     */
    public function withForcedPriceInheritance(Closure $fn)
    {
        $this->forcePriceInheritance = true;

        $return = $fn();

        $this->forcePriceInheritance = false;

        return $return;
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @param $value
     *
     * @internal
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

    private function withFilter(?Closure $filter = null, $query)
    {
        if ($filter) {
            return $filter($query);
        }

        return $query;
    }
}
