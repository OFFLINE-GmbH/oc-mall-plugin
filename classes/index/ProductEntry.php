<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Product;

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

        $product->loadMissing(['brand', 'variants.prices.currency', 'prices.currency', 'property_values.property']);

        $data          = $product->attributesToArray();
        $data['index'] = self::INDEX;

        $data['property_values'] = $this->mapProps($product->property_values);
        $data['prices']          = $this->mapPrices($product);
        if ($product->brand) {
            $data['brand'] = ['id' => $product->brand->id, 'slug' => $product->brand->slug];
        }

        $data['sort_orders'] = $product->getSortOrders();

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

    protected function mapPrices(Product $product): Collection
    {
        $currencies = Currency::getAll();

        return collect($currencies)->mapWithKeys(function ($currency) use ($product) {
            return [$currency['code'] => $product->priceWithMissing($currency['id'])->integer];
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
