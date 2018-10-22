<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;

class ProductEntry implements Entry
{
    const INDEX = 'products';

    protected $product;
    protected $data;

    public function __construct(Product $product)
    {
        $this->product = $product;

        // Make sure variants inherit product data again.
        session()->forget('mall.variants.disable-inheritance');

        $product->loadMissing(['variants.prices.currency', 'prices.currency', 'property_values.property']);

        $data                    = $product->attributesToArray();
        $data['index']           = self::INDEX;
        $data['prices']          = $this->mapPrices($product->prices);
        $data['property_values'] = $this->mapProps($product->property_values);

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
            return $value->pluck('safeValue')->unique()->filter()->values();
        })->filter();
    }
}
