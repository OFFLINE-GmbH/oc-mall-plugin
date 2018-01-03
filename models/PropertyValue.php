<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class PropertyValue extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name', 'value'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'offline_mall_property_values';

    public $belongsTo = [
        'property' => Property::class,
        'variant' => Variant::class,
    ];
}
