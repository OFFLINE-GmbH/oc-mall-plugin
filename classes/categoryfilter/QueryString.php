<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;


use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Models\Property;

class QueryString
{
    use HashIds;

    public function deserialize(array $query): Collection
    {
        $query = collect($query)->mapWithKeys(function ($values, $id) {
            return [$this->decode($id)[0] => $values];
        });

        $properties = Property::whereIn('id', $query->keys())->get();

        return $properties->mapWithKeys(function (Property $property) use ($query) {
            if ($property->filter_type === 'set') {
                return [$property->id => new SetFilter($property->id, $query->get($property->id))];
            } elseif ($property->filter_type === 'range') {
                $values = $query->get($property->id);

                return [$property->id => new RangeFilter($property->id, $values['min'], $values['max'])];
            }
        });
    }

    public function serialize(Collection $filter)
    {
        $filter = $filter->mapWithKeys(function (Filter $filter) {
            return [
                $this->encode($filter->property) => $filter->getValues(),
            ];
        });

        return http_build_query(['filter' => $filter->toArray()]);
    }
}