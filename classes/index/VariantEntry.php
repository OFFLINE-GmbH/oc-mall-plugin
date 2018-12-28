<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Variant;

class VariantEntry implements Entry
{
    const INDEX = 'variants';

    protected $variant;
    protected $data;

    public function __construct(Variant $variant)
    {
        $this->variant = $variant;

        // Make sure variants inherit variant data again.
        session()->forget('mall.variants.disable-inheritance');

        $variant->loadMissing(['prices.currency', 'property_values.property', 'product.brand']);

        $product   = $variant->product;
        $basePrice = $variant->price(Currency::defaultCurrency());

        $data                = $variant->attributesToArray();
        $data['category_id'] = $product->category_id;

        $data['index']           = self::INDEX;
        $data['property_values'] = $this->mapProps($variant->all_property_values);
        $data['sort_orders']     = $product->getSortOrders();
        if ($basePrice) {
            $data['prices'] = $this->mapPrices($variant->prices, $basePrice);
        }

        if ($product->brand) {
            $data['brand'] = ['id' => $product->brand->id, 'slug' => $product->brand->slug];
        }

        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function withData(array $data): Entry
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    protected function mapPrices(?Collection $input, $basePrice): Collection
    {
        if ($input === null) {
            $input = collect();
        }

        $input = $input->keyBy('currency_id');
        
        $currencies = Currency::getAll();

        return collect($currencies)->mapWithKeys(function ($currency) use ($input, $basePrice) {
            $price = optional($input->get($currency['id']))->integer;
            // Calculate missing prices using the currency rate.
            if ($price === null) {
                $price = (int)($basePrice->integer * $currency['rate']);
            }

            return [$currency['code'] => $price];
        });
    }

    protected function mapProps(?Collection $input): Collection
    {
        if ($input === null) {
            return collect();
        }

        return $input->groupBy('property_id')->map(function ($value) {
            return $value->pluck('index_value')->unique()->filter()->values();
        })->filter();
    }
}
