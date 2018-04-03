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
        'describable_id',
        'describable_type',
        'property_id',
    ];
    public $with = ['property'];
    public $table = 'offline_mall_property_values';
    public $belongsTo = [
        'property' => [Property::class, 'deleted' => true],
    ];
    public $attachOne = [
        'image' => File::class,
    ];
    public $morphTo = [
        'describable' => [],
    ];
    public $belongsToMany = [
        'categories' => [
            Category::class,
            'table'    => 'offline_mall_category_property',
            'key'      => 'property_id',
            'otherKey' => 'category_id',
            'pivot'    => ['use_for_variants'],
        ],
    ];

    /**
     * The parent's attribute type is store to make trigger conditions
     * work in the custom backend relationship form.
     *
     * @var string
     */
    public $attribute_type = '';

    /**
     * Returns a raw html presentation of the attribute values.
     * The return value contains raw html and therefore is already escaped.
     * @return string
     */
    public function getDisplayValueAttribute()
    {
        $value = e($this->value);
        if ($this->property->type === 'color') {
            return sprintf(
                '<span class="mall-color-swatch" style="display: inline-block; width: 10px; height: 10px; background: %s"></span>',
                $value
            );
        }

        return $value;
    }
}
