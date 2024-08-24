<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Traits;

use October\Rain\Database\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\PriceCategory;

trait ProductPriceAccessors
{
    /**
     * Undocumented function
     * @param CustomerGroup $group
     * @param mixed $currency
     * @return mixed
     */
    public function groupPrice(CustomerGroup $group, $currency)
    {
        $currency = Currency::resolve($currency);
        $prices = $this->customer_group_prices;
        $filter = fn ($query) => $query->where('customer_group_id', $group->id);

        $price = $this->withFilter($filter, $prices->where('currency_id', $currency->id))->first();

        if ($price) {
            return $price;
        }

        return $this->nullPrice(
            $currency,
            $this->withFilter($filter, $prices),
            'customer_group_prices',
            $filter
        );
    }

    /**
     * Undocumented function
     * @param int|PriceCategory $category
     * @param mixed $currency
     * @return mixed
     */
    public function additionalPrice($category, $currency = null)
    {
        if ($category instanceof PriceCategory) {
            $category = $category->id;
        }

        $currency = Currency::resolve($currency);
        $prices = $this->additional_prices;
        $filter = fn ($query) => $query->where('price_category_id', $category);

        $price = $this->withFilter($filter, $prices->where('currency_id', $currency->id))->first();

        if ($price) {
            return $price;
        }

        return $this->nullPrice(
            $currency,
            $this->withFilter($filter, $prices),
            'additional_prices',
            $filter
        );
    }

    /**
     * Undocumented function
     * @deprecated 3.2.2 - The oldPrice category has been made optional.
     * @return mixed
     */
    public function oldPriceRelations()
    {
        $oldPrice = PriceCategory::where('code', 'old_price')->first();

        if ($oldPrice) {
            return $this->additional_prices->where('price_category_id', $oldPrice->id);
        }

        return new Collection();
    }

    /**
     * Undocumented function
     * @deprecated 3.2.2 - The oldPrice category has been made optional.
     * @param mixed $currency
     * @return mixed
     */
    public function oldPrice($currency = null)
    {
        $oldPrice = PriceCategory::where('code', 'old_price')->first();

        if ($oldPrice) {
            return $this->additionalPrice($oldPrice, $currency);
        }

        return null;
    }
    
    /**
     * Undocumented function
     * @deprecated 3.2.2 - The oldPrice category has been made optional.
     * @return mixed
     */
    public function getOldPriceAttribute()
    {
        return $this->mapCurrencyPrices($this->oldPriceRelations());
    }

    /**
     * Undocumented function
     * @deprecated 3.2.2 - The oldPrice category has been made optional.
     * @return boolean
     */
    public function getOnSaleAttribute(): bool
    {
        $price = $this->old_price;

        return $price && $price->count() > 0;
    }
}
