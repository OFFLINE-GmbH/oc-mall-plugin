<?php

namespace OFFLINE\Mall\Classes\Index;

use Event;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Variant;

class VariantEntry implements Entry
{
    public const INDEX = 'variants';

    protected $variant;

    protected $data;

    protected $defaultCurrency;

    public function __construct(Variant $variant)
    {
        $this->variant = $variant;

        // Make sure variants inherit variant data again.
        session()->forget('mall.variants.disable-inheritance');

        $product = $variant->product;

        $this->defaultCurrency = Currency::defaultCurrency();

        $data              = $variant->attributesToArray();
        $data['created_at'] = optional($variant->created_at)->format('Y-m-d H:i:s');
        $data['published'] = $variant->published && $product->published;
        $data['on_sale']   = $variant->on_sale;

        $data['category_id'] = $product->categories->pluck('id');

        $data['index']                 = self::INDEX;
        $data['property_values']       = $this->mapProps($variant->all_property_values);
        $data['sort_orders']           = $product->getSortOrders();
        $data['prices']                = $this->mapPrices($variant);
        $data['parent_prices']         = $this->mapPrices($product);
        $data['customer_group_prices'] = $this->mapCustomerGroupPrices($variant);

        if ($product->brand) {
            $data['brand'] = ['id' => $product->brand->id, 'slug' => $product->brand->slug];
        }

        $result = Event::fire('mall.index.extendVariant', [$product, $variant]);

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

    protected function mapPrices($variant): Collection
    {
        return $variant->withForcedPriceInheritance(fn () => Currency::get()->mapWithKeys(fn ($currency) => [$currency->code => $variant->price($currency)->integer]));
    }

    protected function mapCustomerGroupPrices($model): Collection
    {
        return CustomerGroup::get()->mapWithKeys(function ($group) use ($model) {
            return [
                $group->id => Currency::get()->mapWithKeys(function ($currency) use ($model, $group) {
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

        return $input->groupBy('property_id')->map(fn ($value) => $value->pluck('index_value')->unique()->filter(fn ($item) => !empty($item) || $item === 0 || $item === '0')->values())->filter();
    }
}
