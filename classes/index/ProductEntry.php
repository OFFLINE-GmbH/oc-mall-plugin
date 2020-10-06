<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Product;
use Event;

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

        $data                = $product->attributesToArray();
        $data['index']       = self::INDEX;
        $data['on_sale']     = $product->on_sale;
        $data['category_id'] = $product->categories->pluck('id');

        $data['property_values']       = $this->mapProps($product->property_values);
        $data['prices']                = $this->mapPrices($product);
        $data['customer_group_prices'] = $this->mapCustomerGroupPrices($product);
        if ($product->brand) {
            $data['brand'] = ['id' => $product->brand->id, 'slug' => $product->brand->slug];
        }

        $data['sort_orders'] = $product->getSortOrders();

        $result = Event::fire('mall.index.extendProduct', [$product]);

        if ($result && is_array($result) && $filtered = array_filter($result)) {
            $this->data = array_merge(...$filtered) + $data;
        } else {
            $this->data = $data;
        }
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
        return $product->withForcedPriceInheritance(function() use ($product) {
            return Currency::getAll()->mapWithKeys(function ($currency) use ($product) {
                return [$currency->code => $product->price($currency)->integer];
            });
        });
    }

    protected function mapCustomerGroupPrices($model): Collection
    {
        return CustomerGroup::get()->mapWithKeys(function ($group) use ($model) {
            return [
                $group->id => Currency::getAll()->mapWithKeys(function ($currency) use ($model, $group) {
                    $price = $model->groupPrice($group, $currency);
                    if ($price) {
                        return [$price->currency->code => $price->integer];
                    }

                    return null;
                })->filter(),
            ];
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
