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

        $properties = $category->load('property_groups.properties')->properties->whereIn('slug', $query->keys());

        return $properties->map(function (Property $property) use ($query) {
            if ($property->pivot->filter_type === 'set') {
                return new SetFilter($property, $query->get($property->slug));
            }
            if ($property->pivot->filter_type === 'range') {
                $values = $query->get($property->slug);

                return new RangeFilter(
                    $property,
                    $values['min'] ?? null,
                    $values['max'] ?? null
                );
            }
        });
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
