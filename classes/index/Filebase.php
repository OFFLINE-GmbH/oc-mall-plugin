<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use Nahid\JsonQ\Jsonq;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Models\Currency;

class Filebase implements Index
{
    protected $db;
    protected $jsonq;
    protected $dir;

    public function __construct()
    {
        $this->create('');

        $this->db = new \Filebase\Database([
            'dir'    => $this->dir,
            'pretty' => false,
        ]);

        $this->jsonq = new Jsonq();

        $this->addMacros();
    }

    public function insert(string $index, Entry $entry)
    {
        $data = $entry->data();
        $item = $this->db->get($this->key($index, $data['id']));

        return $item->save($data);
    }

    public function update(string $index, $id, Entry $entry)
    {
        $data = $entry->data();
        $item = $this->db->get($this->key($index, $id));

        return $item->save($data);
    }

    public function delete(string $index, $id)
    {
        $item = $this->db->get($this->key($index, $id));
        if (count($item->getData()) < 1) {
            // The item is not existing in the index and doesn't have to be deleted.
            return;
        }

        return $item->delete();
    }

    public function create(string $index)
    {
        $dir = storage_path('app/index');
        if ( ! is_dir($dir)) {
            mkdir($dir);
        }
        $this->dir = $dir;
    }

    public function drop(string $index)
    {
        $pattern = sprintf('%s/%s-*.json', $this->dir, $index);
        foreach (glob($pattern) as $file) {
            unlink($file);
        }
    }

    public function fetch(string $index, Collection $filters, SortOrder $order, int $perPage, int $forPage): IndexResult
    {
        $skip  = $perPage * ($forPage - 1);
        $items = $this->search($index, $filters, $order);

        $slice = array_map(function ($item) {
            return $item['id'];
        }, array_slice($items, $skip, $perPage));

        return new IndexResult($slice, count($items));
    }

    protected function search(string $index, Collection $filters, SortOrder $order)
    {
        $this->jsonq->collect($this->db->query()->results());
        $this->jsonq->where('index', '=', $index);
        $this->jsonq->where('published', '=', true);

        $filters = $this->applySpecialFilters($filters);
        $this->applyCustomFilters($filters);

        if (\is_callable($order->customSortFunction())) {
            $this->jsonq->sortByCallable(
                $order->customSortFunction($order->property(), $order->direction())
            );
        } else {
            $this->jsonq->sortBy($order->property(), $order->direction());
        }

        return $this->jsonq->get();
    }

    protected function applySpecialFilters(Collection $filters): Collection
    {
        if ($filters->has('category_id')) {
            $filter = $filters->pull('category_id');
            $this->jsonq->where($filter->property, 'includes', $filter->values());
        }

        if ($filters->has('brand')) {
            $filter = $filters->pull('brand');
            $this->jsonq->whereIn('brand.slug', $filter->values());
        }

        if ($filters->has('on_sale')) {
            $filters->pull('on_sale');
            $this->jsonq->where('on_sale', true);
        }

        if ($filters->has('price')) {
            $price    = $filters->pull('price');
            $currency = Currency::activeCurrency()->code;

            ['min' => $min, 'max' => $max] = $price->values();

            $this->jsonq->where('prices.' . $currency, '>=', (int)($min * 100));
            $this->jsonq->where('prices.' . $currency, '<=', (int)($max * 100));
        }

        return $filters;
    }

    protected function applyCustomFilters(Collection $filters)
    {
        $filters->each(function (Filter $filter) {
            if ($filter instanceof SetFilter) {
                $this->jsonq->where('property_values.' . $filter->property->id, 'includes', $filter->values());
            }
            if ($filter instanceof RangeFilter) {
                $this->jsonq->where('property_values.' . $filter->property->id, 'includes between', [
                    $filter->minValue,
                    $filter->maxValue,
                ]);
            }
        });
    }

    protected function key(string $index, $id): string
    {
        return $index . '-' . $id;
    }

    protected function addMacros()
    {
        $this->jsonq::macro('includes', function ($val, $comp) {
            if (is_array($val) && count($val) > 0) {
                if (is_array($val[0])) {
                    $val = array_map('json_encode', $val);
                }

                return count(array_intersect($val, $comp)) > 0;
            }

            return in_array($val, $comp);
        });

        $this->jsonq::macro('includes between', function ($val, $comp) {
            foreach ($val as $value) {
                if ($value >= $comp[0] && $value <= $comp[1]) {
                    return true;
                }
            }

            return false;
        });
    }
}
