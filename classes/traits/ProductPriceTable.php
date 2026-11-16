<?php

namespace OFFLINE\Mall\Classes\Traits;

use Backend\Widgets\Table;
use DB;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Database\IsStatesScope;
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
        if (!isset($this->vars['pricetable'])) {
            $this->preparePriceTable();
        }

        return $this->makePartial('price_table_modal', ['widget' => $this->vars['pricetable']]);
    }

    public function onPriceTablePersist()
    {
        DB::transaction(function () {
            $state                     = $this->getPriceTableState();
            $currencies                = Currency::get()->keyBy('code');
            $hasPriceInDefaultCurrency = false;

            $this->loadPriceCategories();

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

            if (! $hasPriceInDefaultCurrency) {
                throw new ValidationException(['prices' => trans('offline.mall::lang.common.price_missing')]);
            }
        });

        // Reindex product and variants.
        (new ProductObserver(app(Index::class)))->updated(Product::find($this->params[0]));
    }

    protected function getPriceTableState(): array
    {
        // A single JSON field stays below PHP's max_input_vars, nested form fields get silently truncated.
        $json = post('state_json');

        if (is_string($json) && $json !== '') {
            $state = json_decode($json, true);

            if (! is_array($state)) {
                throw new ApplicationException('Invalid price table data.');
            }

            // Form encoding sent empty cells as '', keep that so no NULL price rows get created.
            array_walk_recursive($state, function (&$value) {
                $value = $value ?? '';
            });

            return $state;
        }

        return post('state', []);
    }

    protected function loadPriceCategories()
    {
        $this->vars['additionalPriceCategories'] = PriceCategory::orderBy('sort_order', 'ASC')->get();
        $this->vars['customerGroups']            = CustomerGroup::orderBy('sort_order', 'ASC')->get();
    }

    protected function preparePriceTable()
    {
        $config = $this->makeConfig($this->productPriceTableConfig);

        $this->loadPriceCategories();

        $this->vars['additionalPriceCategories']->each(function (PriceCategory $category) use ($config) {
            $config->columns['additional__' . $category->id] = ['title' => $category->name];
        });

        $this->vars['customerGroups']->each(function (CustomerGroup $group) use ($config) {
            $config->columns['group__' . $group->id] = [
                'title' => sprintf('%s %s', trans('offline.mall::lang.product.price'), $group->name),
            ];
        });

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
        $this->vars['pricetableState'] = $this->processTableData($tableData)->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
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

        $model        = (new $type())->find($record['original_id']);
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

        if (! $price) {
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
            Price::withoutGlobalScope(new IsStatesScope())->create([
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
        $ids = [];

        foreach ($state as $currency => $items) {
            $currencyId = $currencies->get($currency)->id;

            foreach ($items as $record) {
                $type = $record['type'] === 'product' ? Product::class : Variant::class;
                $ids[$type::MORPH_KEY][$currencyId][] = $record['original_id'];
            }
        }

        foreach ($ids as $morphKey => $byCurrency) {
            foreach ($byCurrency as $currencyId => $priceableIds) {
                foreach (array_chunk($priceableIds, 500) as $chunk) {
                    $scope = function ($q) use ($morphKey, $currencyId, $chunk) {
                        $q->where('priceable_type', $morphKey)
                            ->where('currency_id', $currencyId)
                            ->whereIn('priceable_id', $chunk);
                    };
                    Price::where($scope)->delete();
                    CustomerGroupPrice::where($scope)->delete();
                }
            }
        }
    }
}
