<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\HashIds;
use System\Models\File;

class PropertyValue extends Model
{
    use Validation;
    use HashIds;

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
     * The parent's attribute type is stored to make trigger conditions
     * work in the custom backend relationship form.
     *
     * @var string
     */
    public $attribute_type = '';

    public function setValueAttribute($value)
    {
        // Some attribute types (like color) have multiple values (a hex color and a display name)
        // These types of attribute values are stored as json string.
        $this->attributes['value'] = \is_array($value)
            ? json_encode($value)
            : $value;
    }

    public function getValueAttribute()
    {
        return optional($this->property)->type === 'color'
            ? json_decode($this->getOriginal('value'), true)
            : $this->getOriginal('value');
    }

    /**
     * This attribute can be used if a safe string value
     * is needed even if the value is an array.
     */
    public function getSafeValueAttribute(): string
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
        if ($this->property->type === 'color') {
            return sprintf(
                '<span class="mall-color-swatch" style="display: inline-block; width: 12px; height: 12px; background: %s" title="%s"></span>',
                $this->value['hex'],
                $this->value['name'] ?? ''
            );
        }

        return e($this->value);
    }
}
