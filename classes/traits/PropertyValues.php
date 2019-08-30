<?php


namespace OFFLINE\Mall\Classes\Traits;


use OFFLINE\Mall\Models\PropertyValue;

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
}