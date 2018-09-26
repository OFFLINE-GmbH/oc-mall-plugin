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
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        $prices = $this->customer_group_prices;

        return $prices->where('currency_id', $currency)->where('customer_group_id', $group)->first()
            ?? $this->nullPrice($currency);
    }

    public function additionalPrice($category, $currency = null)
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
        if ($category instanceof PriceCategory) {
            $category = $category->id;
        }

        $prices = $this->additional_prices;

        return $prices->where('currency_id', $currency)->where('price_category_id', $category)->first()
            ?? $this->nullPrice($currency);
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
