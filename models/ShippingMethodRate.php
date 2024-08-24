<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;

class ShippingMethodRate extends Model
{
    use Validation;
    use PriceAccessors;

    /**
     * Morph key as used on the respective relationships.
     * @var string
     */
    public const MORPH_KEY = 'mall.shipping_method_rate';
    
    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_shipping_method_rates';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'from_weight' => 'integer|min:0',
        'to_weight'   => 'min:0',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'from_weight',
        'to_weight',
        'shipping_method_id',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'from_weight' => 'int',
        'to_weight'   => 'int',
    ];

    /**
     * The relations to eager load on every query.
     * @var array
     */
    public $with = ['prices'];

    /**
     * The belongsTo relationships of this model.
     * @var array
     */
    public $belongsTo = [
        'shipping_method' => ShippingMethod::class,
    ];

    /**
     * The morphMany relationships of this model.
     * @var array
     */
    public $morphMany = [
        'prices' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
    ];
}
