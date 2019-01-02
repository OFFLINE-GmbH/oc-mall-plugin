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
    protected $defaultCurrency;

    public function __construct(Variant $variant)
    {
        $this->variant = $variant;

        // Make sure variants inherit variant data again.
        session()->forget('mall.variants.disable-inheritance');

        $variant->loadMissing(['prices.currency', 'property_values.property', 'product.brand']);

        $product = $variant->product;

        $this->defaultCurrency = Currency::defaultCurrency();

        $data                = $variant->attributesToArray();
        $data['category_id'] = $product->category_id;

        $data['index']           = self::INDEX;
        $data['property_values'] = $this->mapProps($variant->all_property_values);
        $data['sort_orders']     = $product->getSortOrders();
        $data['prices']          = $this->mapPrices($variant);
        $data['parent_prices']   = $this->mapPrices($product);

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

    protected function mapPrices($model): Collection
    {
        return $model->prices->mapWithKeys(function ($price) {
            return [$price->currency->code => $price->integer];
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
