<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\HashIds;
use System\Models\File;

class PropertyValue extends Model
{
    use Validation;
    use HashIds;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        ['value', 'index' => true, 'fallback' => false],
    ];

    public $rules = [
    ];

    public $fillable = [
        'value',
        'product_id',
        'variant_id',
        'property_id',
    ];

    public $with = ['property'];

    public $table = 'offline_mall_property_values';

    public $belongsTo = [
        'property' => [Property::class, 'deleted' => true],
        'product'  => [Product::class],
        'variant'  => [Variant::class],
    ];

    public $attachOne = [
        'image' => File::class,
    ];

    /**
     * These types must never be translated.
     */
    public $untranslatableTypes = ['checkbox', 'switch', 'integer', 'float'];

    /**
     * The parent's attribute type is stored to make trigger conditions
     * work in the custom backend relationship form.
     *
     * @var string
     */
    public $attribute_type = '';

    /**
     * Scope that selects only property values that are assigned
     * to a Product, not to a Variant.
     * @param mixed $query
     */
    public function scopeProductOnly($query)
    {
        $query->whereNull('variant_id');
    }

    public function beforeSave()
    {
        $value = $this->attributes['value'] ?? '';

        if ($this->isColor()) {
            $decoded = $this->jsonDecodeValue();
            $value = $decoded['name'] ?? $decoded['hex'] ?? '';
        }

        $this->index_value = str_slug($value);
    }

    public function afterSave(): void
    {
        UniquePropertyValue::updateUsingPropertyValue($this);
    }

    public function setValueAttribute($value)
    {
        if (in_array(optional($this->property)->type, $this->untranslatableTypes)) {
            $this->translatable = [];
        }

        $this->attributes['value'] = $this->handleArrayValue($value);
    }

    public function isColor()
    {
        return optional($this->property)->type === 'color';
    }

    public function getValueAttribute()
    {
        $type  = optional($this->property)->type;

        if (in_array($type, $this->untranslatableTypes)) {
            $value = $this->original['value'] ?? $this->attributes['value'];
        } else {
            $value = $this->getAttributeTranslated('value');
        }

        if ($type === 'float') {
            return (float)$value;
        }

        if ($type === 'integer') {
            return (int)$value;
        }

        if ($type === 'checkbox' || $type === 'switch') {
            return (bool)$value;
        }

        if ($type === 'color') {
            return $this->jsonDecodeValue($value);
        }

        return $value;
    }

    /**
     * This attribute can be used if a safe string value
     * is needed even if the value is an array.
     */
    public function getSafeValueAttribute()
    {
        return \is_array($this->value) ? json_encode($this->value) : $this->value;
    }

    /**
     * Returns a raw html presentation of the attribute values.
     * The return value contains raw html and therefore is already escaped.
     * @return string
     */
    public function getDisplayValueAttribute()
    {
        $value = $this->getAttributeTranslated('value');

        if ($this->isColor()) {
            $value = $this->jsonDecodeValue($value);

            return sprintf(
                '<span class="mall-color-swatch" style="display: inline-block; width: 12px; height: 12px; background: %s" title="%s"></span>',
                $value['hex'] ?? 'unknown',
                $value['name'] ?? ''
            );
        }

        $type  = optional($this->property)->type;

        if ($type === 'checkbox') {
            $key = (bool)$value ? 'yes' : 'no';

            return trans('offline.mall::lang.common.' . $key);
        }

        return e($value);
    }

    public function getValueAttributeTranslated($locale)
    {
        $value = $this->noFallbackLocale()->getAttributeTranslated('value', $locale);

        if ($this->isColor()) {
            // Only the name attribute is translatable for a color value.
            // We only return the name so the MLText form widget displays
            // the correct value in the backend form.
            $value = $this->jsonDecodeValue($value);

            return $value['name'] ?? '';
        }

        return $value;
    }

    /**
     * Handle special array property values.
     * @param $value
     * @return mixed
     */
    public function handleArrayValue($value)
    {
        if ($this->isColor()) {
            $name = $value['name'] ?? false;
            $hex  = $value['hex'] ?? false;

            // If both keys are empty store this value as null.
            if (! $name && ! $hex) {
                return null;
            }
        }

        // Some attribute types (like color) have multiple values (a hex color and a display name)
        // These types of attribute values are stored as json string.
        return \is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Returns the decoded json value.
     *
     * @param null $value
     *
     * @return mixed
     */
    private function jsonDecodeValue($value = null)
    {
        $value ??= $this->attributes['value'];

        if (! $value) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true);
    }
}
