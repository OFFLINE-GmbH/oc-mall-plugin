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

            return [$prop => new $type($prop, array_values($query->get($prop)))];
        });

        $properties = $category->load('property_groups.properties')->properties->whereIn('slug', $query->keys());

        // Map the user defined database properties.
        return $properties->mapWithKeys(function (Property $property) use ($query) {
            if ($property->pivot->filter_type === 'set') {
                return [$property->slug => new SetFilter($property, $query->get($property->slug))];
            }
            if ($property->pivot->filter_type === 'range') {
                $values = $query->get($property->slug);

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

    public function serialize(Collection $filter)
    {
        $filter = $filter->mapWithKeys(function (Filter $filter, $property) {
            return [
                $property => $filter->values(),
            ];
        });

        return http_build_query(['filter' => $filter->toArray()]);
    }
}
