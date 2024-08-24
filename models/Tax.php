<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use Model;
use October\Rain\Database\Collection;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Database\IsStates;
use Rainlab\Location\Models\Country as RainLabCountry;

class Tax extends Model
{
    use IsStates;
    use Validation;

    /**
     * Default cache key for the queries taxes.
     * @var string
     */
    public const DEFAULT_TAX_CACHE_KEY = 'mall.taxes.default';

    /**
     * Disable `is_default` handler on IsStates trait. Even if Tax uses a default value, the
     * current IsStates trait does not support multiple defaults, especially when using an
     * additional linking table (`offline_mall_country_tax`).
     * @var null|string
     */
    public const IS_DEFAULT = null;

    /**
     * Enable `is_enabled` handler on IsStates trait, by passing the column name.
     * @var null|string
     */
    public const IS_ENABLED = 'is_enabled';

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];
    
    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_taxes';
    
    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'          => 'required',
        'percentage'    => 'numeric|min:0|max:100',
        'is_enabled'    => 'nullable|boolean',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'percentage',
        'is_default',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * The belongsToMany relationships of this model.
     * @var array
     */
    public $belongsToMany = [
        'products'         => [
            Product::class,
            'table'    => 'offline_mall_product_tax',
            'key'      => 'tax_id',
            'otherKey' => 'product_id',
        ],
        'shipping_methods' => [
            ShippingMethod::class,
            'table'    => 'offline_mall_shipping_method_tax',
            'key'      => 'tax_id',
            'otherKey' => 'shipping_method_id',
        ],
        'payment_methods'  => [
            PaymentMethod::class,
            'table'    => 'offline_mall_payment_method_tax',
            'key'      => 'tax_id',
            'otherKey' => 'payment_method_id',
        ],
        'countries'        => [
            RainLabCountry::class,
            'table'      => 'offline_mall_country_tax',
            'key'        => 'tax_id',
            'otherKey'   => 'country_id',
            'conditions' => 'is_enabled = true',
        ],
    ];

    /**
     * Returns the default taxes.
     * @return Collection<Tax>|Tax[]
     */
    public static function defaultTaxes(): Collection
    {
        $taxes = Cache::rememberForever(static::DEFAULT_TAX_CACHE_KEY, function () {
            $columns = [ 'id',  'name',  'percentage',  'is_default', 'is_enabled'];
            $taxes = static::where('is_default', true)->get($columns);

            if (!$taxes) {
                return [];
            } else {
                // Make sure the "translations" relation is not cached.
                return $taxes->map->only($columns)->toArray();
            }
        });

        if (!$taxes) {
            return new Collection();
        } else {
            return self::hydrate($taxes);
        }
    }

    /**
     * Hook after model has been saved.
     * @return void
     */
    public function afterSave()
    {
        Cache::forget(self::DEFAULT_TAX_CACHE_KEY);
    }

    /**
     * Get percentage decimal attribute.
     * @return float
     */
    public function getPercentageDecimalAttribute()
    {
        return (float)$this->percentage / 100;
    }
}
