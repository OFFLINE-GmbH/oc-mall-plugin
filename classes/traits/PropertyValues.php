<?php


namespace OFFLINE\Mall\Classes\Traits;


use October\Rain\Support\Collection;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

trait PropertyValues
{
    /**
     * Returns a collection that contains a "group" and "values"
     * key for each available property value.
     *
     * @return Collection
     */
    public function getGroupedPropertiesAttribute()
    {
        if ($this instanceof Variant) {
            $this->loadMissing([
                'product_property_values.property.property_groups',
                'property_values.property.property_groups',
            ]);
        } else {
            $this->loadMissing('property_values.property.property_groups');
        }

        $category = $this->categories->first();
        $groups = $this->all_property_values->map(function($value) use ($category) {
            return optional($value->property->property_groups)->first(function($group) use ($category) {
                // Select the first property group that is assigned to the product's category.
                // Fallback to the first property group that is assigned.
                return $category ? $category->inherited_property_groups->contains($group) : true;
            });
        })->unique();

        if ($groups->count() < 1) {
            return new Collection();
        }

        // Sort the property groups by the categories' pivot sort order.
        if ($category) {
            $order = optional($category->inherited_property_groups)->pluck('id', 'pivot.relation_sort_order');

            $groups = $groups->sortBy(function ($group) use ($order) {
                return $order[$group->id] ?? 0;
            });
        }

        return $groups->map(function(PropertyGroup $group) {
            return collect([
                'group' => $group,
                'values' => $this->all_property_values->filter(function(PropertyValue $value) use ($group) {
                    return $value->property->property_groups->contains($group->id);
                }),
            ]);
        });
    }

    public function getPropertyValue($id)
    {
        return optional($this->all_property_values->where('property_id', $id))->first();
    }

    public function getPropertyValueByName($name)
    {
        return optional($this->all_property_values->where('property.name', $name))->first();
    }

    public function getPropertyValueBySlug($slug)
    {
        return optional($this->all_property_values->where('property.slug', $slug))->first();
    }

    public function getPropertiesDescriptionAttribute()
    {
        return $this->propertyValuesAsString();
    }

    public function propertyValuesAsString()
    {
        return $this->property_values
            ->reject(function (PropertyValue $value) {
                return $value->value === '' || $value->value === null || $value->property === null;
            })
            ->map(function (PropertyValue $value) {
                // display_value is already escaped in PropertyValue::getDisplayValueAttribute()
                return trim(sprintf('%s: %s %s', e($value->property->name), $value->display_value, e($value->property->unit)));
            })->implode('<br />');
    }

    /**
     * Apply transforms to the translated value field of a PropertyValue.
     */
    protected function handleTranslatedPropertyValue(
        Property $property,
        PropertyValue $propertyValue,
        $originalValue,
        $transValue,
        string $locale
    ): ?string {
        if ($property->type === 'color') {
            // The hex value is not translatable, so just copy it from the original value.
            $transValue['hex'] = $originalValue['hex'] ?? '';
        }
        // Make sure array values are json encoded.
        $transValue = $propertyValue->handleArrayValue($transValue);
        // If this is a dropdown type, we need to fetch the translated option manually from the
        // repeater data of another locale.
        if ($property->type === 'dropdown') {
            // Get the current index of the option in the repeater values.
            $index = collect($property->options)->pluck('value')->search($transValue);
            // Get the same index from the translated repeater values.
            $transValue = collect($property->getAttributeTranslated('options', $locale))
                ->pluck('value')
                ->get($index);
        }

        // Use the original value as fallback value if no translation was entered.
        if ($transValue === '' || $transValue === null) {
            return $propertyValue->handleArrayValue($originalValue);
        }
        return $transValue;
    }
}