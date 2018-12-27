<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Models\ProductPrice;

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

        $variant->loadMissing(['prices.currency', 'property_values.property']);

        $data                    = $variant->attributesToArray();
        $data['category_id']     = $variant->product->category_id;
        $data['brand_id']        = $variant->product->brand_id;
        $data['index']           = self::INDEX;
        $data['prices']          = $this->mapPrices($variant->prices);
        $data['property_values'] = $this->mapProps($variant->all_property_values);
        $data['sort_orders']     = $variant->product->getSortOrders();

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

    protected function mapPrices(?Collection $input): Collection
    {
        if ($input === null) {
            return collect();
        }

        return $input->mapWithKeys(function (ProductPrice $price) {
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
