<?php


namespace OFFLINE\Mall\Classes\Traits;

use Backend\Widgets\Table;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomerGroupPrice;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\PriceCategory;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Variant;

trait ProductPriceTable
{
    public function onLoadPriceTable()
    {
        return $this->makePartial('price_table_modal', ['widget' => $this->vars['pricetable']]);
    }

    protected function preparePriceTable()
    {
        $config = $this->makeConfig($this->productPriceTableConfig);

        $additionalPriceCategories = PriceCategory::orderBy('sort_order', 'ASC')->get();
        $additionalPriceCategories->each(function (PriceCategory $category) use ($config) {
            $config->columns['additional__' . $category->id] = ['title' => $category->name];
        });
        $this->vars['additionalPriceCategories'] = $additionalPriceCategories;

        $customerGroups = CustomerGroup::orderBy('sort_order', 'ASC')->get();
        $customerGroups->each(function (CustomerGroup $group) use ($config) {
            $config->columns['group__' . $group->id] = [
                'title' => sprintf('%s %s', trans('offline.mall::lang.product.price'), $group->name),
            ];
        });
        $this->vars['customerGroups'] = $customerGroups;

        $widget = $this->makeFormWidget(Table::class, $config);
        $widget->bindToController();

        $model = Product::with([
            'prices',
            'additional_prices',
            'customer_group_prices',
            'variants.customer_group_prices',
            'variants.additional_prices',
        ])->find($this->params[0]);

        $tableData = collect([$model]);
        if ($model->inventory_management_method === 'variant') {
            $tableData = $model->variants->prepend($model);
        }

        $this->vars['pricetable']      = $widget;
        $this->vars['currencies']      = Currency::orderBy('is_default', 'DESC')->orderBy('sort_order', 'ASC')->get();
        $this->vars['pricetableState'] = $this->processTableData($tableData)->toJson();
    }

    public function onPriceTablePersist()
    {
        \DB::transaction(function () {
            $state                     = post('state', []);
            $currencies                = Currency::get()->keyBy('code');
            $hasPriceInDefaultCurrency = false;

            $this->removeOldPricingInformation($state, $currencies);

            foreach ($state as $currency => $records) {
                $currency = $currencies->get($currency);
                foreach ($records as $record) {
                    if ($this->hasDefaultCurrencyPrice($record, $currency)) {
                        $hasPriceInDefaultCurrency = true;
                    }
                    $this->persistPriceTableRow($record, $currency);
                }
            }

            if ( ! $hasPriceInDefaultCurrency) {
                throw new ValidationException(['prices' => trans('offline.mall::lang.common.price_missing')]);
            }
        });

        // Reindex product and variants.
        (new ProductObserver(app(Index::class)))->updated(Product::find($this->params[0]));
    }

    protected function hasDefaultCurrencyPrice($record, $currency)
    {
        return $record['type'] === 'product'
            && $currency->id === Currency::defaultCurrency()->id
            && $record['price'] !== ''
            && $record['price'] !== null;
    }

    protected function persistPriceTableRow($record, $currency)
    {
        $type = $record['type'] === 'product' ? Product::class : Variant::class;

        $model        = (new $type)->find($record['original_id']);
        $model->stock = $record['stock'] ?? null;
        $model->save();

        $this->persistPrices($record, $currency, $model);
        $this->persistCustomerGroupPrices($record, $currency);
        $this->persistAdditionalPriceCategories($record, $currency);
    }

    protected function persistPrices($record, $currency, $model)
    {
        $type  = $record['type'] === 'product' ? Product::class : Variant::class;
        $price = $model->prices->where('currency_id', $currency->id);
        $price = $type === Variant::class
            ? $price->firstWhere('variant_id', $record['original_id'])
            : $price->firstWhere('variant_id', null);

        if ( ! $price) {
            $productId = $type === Variant::class ? Variant::find($record['original_id'])->product->id : null;
            $price     = ProductPrice::make([
                'variant_id'  => $type === Product::class ? null : $record['original_id'],
                'product_id'  => $type === Product::class ? $record['original_id'] : $productId,
                'currency_id' => $currency->id,
            ]);
        }
        $price->price = $record['price'];
        $price->save();
    }

    protected function persistCustomerGroupPrices($record, $currency)
    {
        $type = $record['type'] === 'product' ? Product::class : Variant::class;

        foreach ($this->vars['customerGroups'] as $group) {
            $price = $record['group__' . $group['id']] ?? false;
            if ($price === false || $price === null) {
                continue;
            }
            CustomerGroupPrice::create([
                'customer_group_id' => $group['id'],
                'priceable_type'    => $type::MORPH_KEY,
                'priceable_id'      => $record['original_id'],
                'currency_id'       => $currency->id,
                'price'             => $price,
            ]);
        }
    }

    protected function persistAdditionalPriceCategories($record, $currency)
    {
        $type = $record['type'] === 'product' ? Product::class : Variant::class;

        foreach ($this->vars['additionalPriceCategories'] as $group) {
            $price = $record['additional__' . $group['id']] ?? false;
            if ($price === false || $price === null) {
                continue;
            }
            Price::create([
                'price_category_id' => $group['id'],
                'priceable_type'    => $type::MORPH_KEY,
                'priceable_id'      => $record['original_id'],
                'currency_id'       => $currency->id,
                'price'             => $price,
            ]);
        }
    }

    protected function processTableData($data)
    {
        return $this->vars['currencies']->mapWithKeys(function ($currency) use ($data) {
            return [
                $currency->code => $data->map(function ($item) use ($currency) {
                    $type = $item instanceof Variant ? 'variant' : 'product';
                    $data = [
                        'id'          => $type . '-' . $item->id,
                        'original_id' => $item->id,
                        'type'        => $type,
                        'name'        => $item->name,
                        'stock'       => $item->stock,
                        'price'       => $item->price($currency)->decimal,
                    ];

                    $this->vars['customerGroups']->each(function (CustomerGroup $group) use (&$data, $currency, $item) {
                        $data['group__' . $group->id] = $item->groupPrice($group, $currency)->decimal;
                    });

                    $this->vars['additionalPriceCategories']
                        ->each(function (PriceCategory $category) use (&$data, $currency, $item) {
                            $data['additional__' . $category->id] = $item->additionalPrice(
                                $category->id,
                                $currency
                            )->decimal;
                        });

                    return $data;
                }),
            ];
        });
    }

    protected function removeOldPricingInformation($state, $currencies)
    {
        $groupPrice = CustomerGroupPrice::query();
        $price      = Price::query();
        foreach ($state as $currency => $items) {
            foreach ($items as $record) {
                $closure = function ($q) use ($record, $currencies, $currency) {
                    $type = $record['type'] === 'product' ? Product::class : Variant::class;
                    $q->where('priceable_type', $type::MORPH_KEY)
                      ->where('priceable_id', $record['original_id'])
                      ->where('currency_id', $currencies->get($currency)->id);
                };
                $price->orWhere($closure);
                $groupPrice->orWhere($closure);
            }
        }
        $price->delete();
        $groupPrice->delete();
    }
}
