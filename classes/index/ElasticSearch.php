<?php

namespace OFFLINE\Mall\Classes\Index;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Property;

class ElasticSearch implements Index
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->build();

        $this->create('products');
        $this->create('variants');
    }

    public function insert(string $index, Entry $data)
    {
        $entry = [
            'index' => $index,
            'type'  => $index,
            'id'    => $data->data()['id'],
            'body'  => $data->data(),
        ];
        $this->client->index($entry);
    }

    public function update(string $index, $id, Entry $data)
    {
        $this->insert($index, $data);
    }

    public function delete(string $index, $id)
    {
        $entry = [
            'index' => $index,
            'type'  => $index,
            'id'    => $id,
        ];
        $this->client->delete($entry);
    }

    public function create(string $index)
    {
        if ( ! $this->client->indices()->exists(['index' => $index])) {
            $this->client->indices()->create(['index' => $index]);
            $this->setMapping($index);
        }
    }

    public function setMapping(string $index)
    {
        $this->client->indices()->putMapping([
            'index' => $index,
            'type'  => $index,
            'body'  => [
                'dynamic_templates' => [
                    [
                        'property_values' => [
                            'path_match' => 'property_values.*',
                            'mapping'    => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                ],
                'properties'        => [
                    'property_values' => [
                        'properties' => $this->buildPropertyValueMapping(),
                    ],
                ],
            ],
        ]);
    }

    protected function buildPropertyValueMapping()
    {
        return Property::get()->mapWithKEys(function (Property $property) {

            if ($property->type === 'float') {
                $type = 'float';
            } elseif ($property->type === 'integer') {
                $type = 'integer';
            } else {
                return [];
            }

            return [
                $property->id => [
                    'type' => $type,
                ],
            ];
        })->filter()->toArray();
    }

    public function drop(string $index)
    {
        $this->client->indices()->delete(['index' => $index]);
    }

    public function fetch(
        string $index,
        Collection $filters,
        SortOrder $order,
        int $perPage,
        int $forPage
    ): IndexResult {
        $query = $this->getBaseQuery($order, $perPage, $forPage);
        $query = $this->applySpecialFilters($filters, $query);
        $query = $this->applyCustomFilters($filters, $query);

        $params = [
            'index' => $index,
            'type'  => $index,
            'body'  => $query,
        ];
        $result = $this->client->search($params);
        $hits   = $result['hits']['hits'] ?? [];
        $count  = $result['hits']['total'] ?? 0;

        $ids = array_map(function ($hit) {
            return $hit['_source']['id'];
        }, $hits);

        return new IndexResult($ids, $count);
    }

    protected function applySpecialFilters(Collection $filters, array $query): array
    {
        if ($filters->has('category_id')) {
            $filter = $filters->pull('category_id');

            $query['query']['bool']['filter']['bool']['must'][]['terms']['category_id'] = $filter->values();
        }
        if ($filters->has('brand')) {
            $filter = $filters->pull('brand');

            $query['query']['bool']['filter']['bool']['must'][]['terms']['brand.slug'] = $filter->values();
        }

        if ($filters->has('price')) {
            $price    = $filters->pull('price');
            $currency = Currency::activeCurrency()->code;

            ['min' => $min, 'max' => $max] = $price->values();

            $query['query']['bool']['filter']['bool']['must'][]['range'] = [
                'prices.' . $currency => [
                    'gte' => (int)$min * 100,
                    'lte' => (int)$max * 100,
                ],
            ];
        }

        return $query;
    }

    protected function applyCustomFilters(Collection $filters, array $query): array
    {
        $filters->each(function (Filter $filter) use (&$query) {
            $path = 'property_values.' . $filter->property->id;
            if ($filter instanceof SetFilter) {
                $query['query']['bool']['filter']['bool']['must'][]['terms'] = [
                    $path => $filter->values(),
                ];
            }
            if ($filter instanceof RangeFilter) {
                $query['query']['bool']['filter']['bool']['must'][]['range'] = [
                    $path => [
                        'gte' => $filter->minValue,
                        'lte' => $filter->maxValue,
                    ],
                ];
            }
        });

        return $query;
    }

    protected function getBaseQuery(SortOrder $order, int $perPage, int $forPage): array
    {
        return [
            'sort'  => [
                [
                    $order->property() => [
                        'order' => $order->direction(),
                    ],
                ],
            ],
            'from'  => $perPage * ($forPage - 1),
            'size'  => $perPage,
            'query' => [
                'bool' => [
                    'filter' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'published' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
