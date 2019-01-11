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
    /**
     * Delimiter for range filter values.
     * @var string
     */
    const DELIMITER_RANGE = '-';
    /**
     * Delimiter for set filter values.
     * @var string
     */
    const DELIMITER_SET = '.';

    public function deserialize(array $query, ?Category $category = null): Collection
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

            $values = $this->getPropValues($query->get($prop), $this->getDelimiter($type));

            return [$prop => new $type($prop, array_values($values))];
        });

        if ( ! $category) {
            return $specialProperties;
        }

        $properties = $category->load('property_groups.properties')->properties->whereIn('slug', $query->keys());

        // Map the user defined database properties.
        return $properties->mapWithKeys(function (Property $property) use ($query) {
            $delimiter = $this->getDelimiter($property->pivot->filter_type);
            $values    = $query->get($property->slug);
            if ($property->pivot->filter_type === 'set') {
                $values = $this->getPropValues($values, $delimiter);

                return [$property->slug => new SetFilter($property, $values)];
            }
            if ($property->pivot->filter_type === 'range') {
                $values = $this->getPropValues($values, $delimiter);

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
            $delimiter = $this->getDelimiter(get_class($filter));

            return [
                $property => implode($delimiter, $filter->values()),
            ];
        });

        return http_build_query(array_merge($filter->toArray(), ['sort' => $sortOrder]));
    }

    /**
     * Explode the string version of the property values back
     * into a proper array.
     *
     * @param        $values
     *
     * @param string $delimiter
     *
     * @return array
     */
    protected function getPropValues($values, string $delimiter): array
    {
        $values = explode($delimiter, $values);
        if ($delimiter === '-') {
            return ['min' => $values[0] ?? null, 'max' => $values[1] ?? null];
        }

        return $values;
    }

    /**
     * Delimiter for query string serialisation.
     *
     * @param string $type
     *
     * @return string
     */
    protected function getDelimiter(string $type): string
    {
        if ($type === 'range' || $type === RangeFilter::class) {
            return self::DELIMITER_RANGE;
        }

        return self::DELIMITER_SET;
    }
}
