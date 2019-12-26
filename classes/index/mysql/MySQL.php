<?php

namespace OFFLINE\Mall\Classes\Index\MySQL;

use Cache;
use DB;
use Illuminate\Support\Collection;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Support\Facades\Schema;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\Random;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Classes\Index\Entry;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\IndexNotSupportedException;
use OFFLINE\Mall\Classes\Index\IndexResult;
use OFFLINE\Mall\Models\Currency;
use Throwable;

class MySQL implements Index
{
    const CACHE_KEY = 'offline_mall.mysql.index.exists';

    /**
     * Type casts for index columns.
     * @var array
     */
    public $columnCasts = [
        'prices'      => 'unsigned',
        'sort_orders' => 'unsigned',
    ];

    public function __construct()
    {
        $this->create('');
    }

    protected function db()
    {
        return new IndexEntry();
    }

    public function insert(string $index, Entry $entry)
    {
        $this->persist($index, $entry);
    }

    public function update(string $index, $id, Entry $entry)
    {
        $this->persist($index, $entry);
    }

    protected function persist(string $index, Entry $entry)
    {
        $data = $entry->data();

        $productId = $index === 'products' ? $data['id'] : $data['product_id'];
        $variantId = $index === 'products' ? null : $data['id'];

        $isGhost = false;
        if (starts_with($variantId, 'product-')) {
            $isGhost   = true;
            $productId = str_replace('product-', '', $variantId);
        }

        $published = $data['published'] ?? false;

        $this->db()->updateOrCreate([
            'index'      => $index,
            'product_id' => $productId,
            'variant_id' => $isGhost ? null : $variantId,
            'is_ghost'   => $isGhost,
        ], [
            'name'                  => $data['name'] ?? '',
            'brand'                 => $data['brand']['slug'] ?? '',
            'stock'                 => $data['stock'],
            'reviews_rating'        => $data['reviews_rating'] ?? 0,
            'sales_count'           => $data['sales_count'] ?? 0,
            'on_sale'               => $data['on_sale'] ? 1 : 0,   // Use integer values to not trigger an
            'published'             => $published ? 1 : 0,        // update only because of the true/1 conversion
            'category_id'           => $data['category_id'],
            'property_values'       => $data['property_values'],
            'sort_orders'           => $data['sort_orders'],
            'prices'                => $data['prices'],
            'parent_prices'         => $data['parent_prices'] ?? [],
            'customer_group_prices' => $data['customer_group_prices'] ?? [],
            'created_at'            => $data['created_at'] ?? now(),
        ]);
    }

    public function delete(string $index, $id)
    {
        $col = $index === 'products' ? 'product_id' : 'variant_id';
        // Remove a ghost variant
        if (starts_with($id, 'product-')) {
            $index = 'variants';
            $col   = 'product_id';
            $id    = str_replace('product-', '', $id);
        }
        $this->db()->where('index', $index)->where($col, $id)->delete();
    }

    public function create(string $index)
    {
        if (Cache::has(self::CACHE_KEY)) {
            return;
        }

        $table = $this->db()->table;
        if (Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('variant_id')->nullable();
                $table->string('index');
                $table->string('name', 191);
                $table->string('brand');
                $table->boolean('published');
                $table->integer('stock');
                $table->decimal('reviews_rating', 3, 2);
                $table->integer('sales_count')->default(0);
                $table->boolean('on_sale')->default(0);
                $table->boolean('is_ghost')->default(0);
                $table->json('category_id');
                $table->json('property_values');
                $table->json('sort_orders');
                $table->json('prices');
                $table->json('parent_prices');
                $table->json('customer_group_prices');
                $table->timestamp('created_at');

                $table->index(
                    ['product_id', 'variant_id', 'index', 'is_ghost'],
                    'idx_product_variant_index_is_ghost'
                );
                $table->index(['index', 'published'], 'idx_published_index');
            });
        } catch (Throwable $e) {
            if ($this->jsonUnsupported($e)) {
                throw new IndexNotSupportedException();
            }
        }

        Cache::forever(self::CACHE_KEY, true);
    }

    public function drop(string $index)
    {
        Cache::forget(self::CACHE_KEY);
        Schema::dropIfExists($this->db()->table);
    }

    public function fetch(string $index, Collection $filters, SortOrder $order, int $perPage, int $forPage): IndexResult
    {
        $skip  = $perPage * ($forPage - 1);
        $items = $this->search($index, $filters, $order);

        $slice = array_map(function ($item) {
            return $item->is_ghost ? 'product-' . $item->other_id : $item->id;
        }, array_slice($items, $skip, $perPage));

        return new IndexResult($slice, count($items));
    }

    protected function search(string $index, Collection $filters, SortOrder $order)
    {
        $idCol      = $index === 'products' ? 'product_id' : 'variant_id';
        $otherIdCol = $idCol === 'product_id' ? 'variant_id' : 'product_id';

        $db = DB::table($this->db()->table)->select([
            $idCol . ' as id',
            $otherIdCol . ' as other_id',
            'is_ghost',
        ]);

        $db->where('index', $index)->where('published', true);

        $filters = $this->applySpecialFilters($filters, $db);

        $this->applyCustomFilters($filters, $db);

        $this->handleOrder($order, $db);

        return $db->get()->toArray();
    }

    protected function applySpecialFilters(Collection $filters, $db)
    {
        if ($filters->has('category_id')) {
            $filter = $filters->pull('category_id');
            $db->where(function ($q) use ($filter) {
                foreach ($filter->values() as $value) {
                    $q->orWhereRaw('JSON_CONTAINS(category_id, ?)', json_encode([$value]));
                }
            });
        }

        if ($filters->has('brand')) {
            $filter = $filters->pull('brand');
            $db->whereIn('brand', $filter->values());
        }

        if ($filters->has('on_sale')) {
            $filters->pull('on_sale');
            $db->where('on_sale', true);
        }

        if ($filters->has('price')) {
            $price    = $filters->pull('price');
            $currency = Currency::activeCurrency()->code;

            ['min' => $min, 'max' => $max] = $price->values();

            $db->where(function ($q) use ($currency, $min, $max) {
                $q->whereRaw('JSON_EXTRACT(`prices`, ?) >= ?', [
                    "$." . $currency,
                    (int)($min * 100),
                ]);
                $q->whereRaw('JSON_EXTRACT(`prices`, ?) <= ?', [
                    "$." . $currency,
                    (int)($max * 100),
                ]);
            });
        }

        return $filters;
    }

    protected function applyCustomFilters(Collection $filters, $db)
    {
        $filters->each(function (Filter $filter) use ($db) {
            if ($filter instanceof SetFilter) {
                $db->where(function ($q) use ($filter) {
                    foreach ($filter->values() as $value) {
                        $q->orWhereRaw('JSON_CONTAINS(property_values, ?, ?)', [
                            json_encode([$value]),
                            '$."' . $filter->property->id . '"',
                        ]);
                    }
                });
            }
            if ($filter instanceof RangeFilter) {
                $db->where(function ($q) use ($filter) {
                    $id = $filter->property->id;
                    $q->whereRaw('JSON_EXTRACT(property_values, ?) >= ?', ['$."' . $id . '"[0]', $filter->minValue]);
                    $q->whereRaw('JSON_EXTRACT(property_values, ?) <= ?', ['$."' . $id . '"[0]', $filter->maxValue]);
                });
            }
        });
    }

    protected function handleOrder($order, $db)
    {
        // Nested JSON value
        if ($order instanceof Random) {
            $db->inRandomOrder();
        } elseif (str_contains($order->property(), '.')) {
            $parts = explode('.', $order->property());
            $field = $parts[0];
            array_shift($parts);
            $nested = implode('.', $parts);

            // Apply the right cast for this value. This makes sure, that prices are sorted as floats, not as strings.
            if (isset($this->columnCasts[$field])) {
                $orderBy = sprintf('CAST(JSON_EXTRACT(%s, ?) as %s) %s', DB::raw($field),  $this->columnCasts[$field], $order->direction());
            } else {
                $orderBy = sprintf('JSON_EXTRACT(%s, ?) %s', DB::raw($field), $order->direction());
            }

            $db->orderByRaw($orderBy, ['$.' . '"' . $nested . '"']);
        } else {
            $db->orderBy($order->property(), $order->direction());
        }
    }

    /**
     * Check if the received exception is because of missing
     * JSON support from the used database version.
     *
     * @param $e
     *
     * @return bool
     */
    protected function jsonUnsupported(Throwable $e): bool
    {
        return $e->getCode() === '42000'
            && str_contains($e->getMessage(), ['SQL syntax', 'near \'json']);
    }
}
