<?php


namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

trait PropertyValues
{
    public function getPropertyValue($id)
    {
        return optional($this->all_property_values->where('property_id', $id))->first();
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