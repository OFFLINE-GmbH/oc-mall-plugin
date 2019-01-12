<?php


namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\PriceCategory;

trait ProductPriceAccessors
{
    public function groupPrice(CustomerGroup $group, $currency)
    {
        $currency = Currency::resolve($currency);

        $prices = $this->customer_group_prices;

        $filter = function ($query) use ($group) {
            return $query->where('customer_group_id', $group->id);
        };

        $price = $this->withFilter($filter, $prices->where('currency_id', $currency->id))->first();

        return $price
            ?? $this->nullPrice(
                $currency,
                $this->withFilter($filter, $prices),
                'customer_group_prices',
                $filter
            );
    }

    public function additionalPrice($category, $currency = null)
    {
        $currency = Currency::resolve($currency);

        $prices = $this->additional_prices;

        if ($category instanceof PriceCategory) {
            $category = $category->id;
        }

        $filter = function ($query) use ($category) {
            return $query->where('price_category_id', $category);
        };

        $query = $this->withFilter($filter, $prices->where('currency_id', $currency->id));

        return $query->first()
            ?? $this->nullPrice(
                $currency,
                $this->withFilter($filter, $prices),
                'additional_prices',
                $filter
            );
    }

    public function oldPriceRelations()
    {
        return $this->additional_prices->where('price_category_id', PriceCategory::OLD_PRICE_CATEGORY_ID);
    }

    public function oldPrice($currency = null)
    {
        return $this->additionalPrice(PriceCategory::OLD_PRICE_CATEGORY_ID, $currency);
    }

    public function getOldPriceAttribute()
    {
        return $this->mapCurrencyPrices($this->oldPriceRelations());
    }

    public function getOnSaleAttribute()
    {
        return $this->old_price->count() > 0;
    }
}
