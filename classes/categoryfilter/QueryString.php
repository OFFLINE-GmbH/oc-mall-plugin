<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;

/**
 * This class is used to (de)serialize a `Filter` class into a query string.
 */
class QueryString
{
    public function deserialize(array $query, Category $category): Collection
    {
        $query = collect($query);

        if ($query->count() < 1) {
            return collect([]);
        }

        // Map the special properties since they won't be found in the database.
        $specialProperties = collect(Filter::$specialProperties)->mapWithKeys(function ($type, $prop) use ($query) {
            if ( ! $query->has($prop)) {
                return [];
            }

            $values = $this->getPropValues($query->get($prop));

            return [$prop => new $type($prop, array_values($values))];
        });

        $properties = $category->load('property_groups.properties')->properties->whereIn('slug', $query->keys());

        // Map the user defined database properties.
        return $properties->mapWithKeys(function (Property $property) use ($query) {
            $values = $this->getPropValues($query->get($property->slug));
            if ($property->pivot->filter_type === 'set') {
                return [$property->slug => new SetFilter($property, $values)];
            }
            if ($property->pivot->filter_type === 'range') {
                return [
                    $property->slug => new RangeFilter(
                        $property,
                        [
                            $values['min'] ?? null,
                            $values['max'] ?? null,
                        ]
                    ),
                ];
            }
        })->union($specialProperties);
    }

    public function serialize(Collection $filter, string $sortOrder)
    {
        $filter = $filter->mapWithKeys(function (Filter $filter, $property) {
            return [
                $property => implode('.', $filter->values()),
            ];
        });

        return http_build_query(array_merge($filter->toArray(), ['sort' => $sortOrder]));
    }

    /**
     * Explode the string version of the property values back
     * into a proper array.
     *
     * @param $values
     *
     * @return array
     */
    protected function getPropValues($values): array
    {
        return explode('.', $values);
    }
}
