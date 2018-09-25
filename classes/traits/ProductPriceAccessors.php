<?php


namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\PriceCategory;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait ProductPriceAccessors
{

    public function groupPriceInCurrency($group, $currency)
    {
        if ($group instanceof CustomerGroup) {
            $group = $group->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        $prices = $this->customer_group_prices;

        return optional($prices->where('currency_id', $currency)->where('customer_group_id', $group)->first())
            ->decimal;
    }

    public function additionalPriceInCurrency($category, $currency = null)
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

        return optional($prices->where('currency_id', $currency)->where('price_category_id', $category)->first())
            ->decimal;
    }

    public function oldPriceInCurrencyFormatted($currency = null)
    {
        $price = $this->oldPriceInCurrencyInteger($currency);

        return format_money($price, $this);
    }

    public function oldPriceInCurrencyInteger($currency = null)
    {
        return $this->additionalPriceInCurrency(PriceCategory::OLD_PRICE_CATEGORY_ID, $currency)->integer;
    }

    public function oldPriceInCurrency($currency = null)
    {
        return $this->additionalPriceInCurrency(PriceCategory::OLD_PRICE_CATEGORY_ID, $currency);
    }

    public function oldPrice()
    {
        return $this->additional_prices->where('price_category_id', PriceCategory::OLD_PRICE_CATEGORY_ID);
    }

    public function getOldPriceAttribute()
    {
        return $this->mapCurrencyPrices($this->oldPrice());
    }
}
