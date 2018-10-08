<?php


namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\PriceCategory;

trait ProductPriceAccessors
{
    public function groupPrice($group, $currency)
    {
        if ($group instanceof CustomerGroup) {
            $group = $group->id;
        }

        $currency = Currency::resolve($currency);

        $prices = $this->customer_group_prices;

        return $prices->where('currency_id', $currency->id)->where('customer_group_id', $group)->first()
            ?? $this->nullPrice($currency, $prices->where('customer_group_id', $group));
    }

    public function additionalPrice($category, $currency = null)
    {
        $currency = Currency::resolve($currency);

        $prices = $this->additional_prices;

        return $prices->where('currency_id', $currency->id)->where('price_category_id', $category)->first()
            ?? $this->nullPrice($currency, $prices->where('price_category_id', $category));
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
}
