<?php declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use Illuminate\Support\Facades\Cache;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Collection;
use Rainlab\Location\Models\Country as RainLabCountry;

class Tax extends Model
{
    use Validation;

    public const DEFAULT_TAX_CACHE_KEY = 'mall.taxes.default';

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel'
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
        'is_enabled'    => 'nullable|boolean'
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
    static public function defaultTaxes(): Collection
    {
        $taxes = Cache::rememberForever(static::DEFAULT_TAX_CACHE_KEY, function () {
            $columns = [ 'id',  'name',  'percentage',  'is_default', 'is_enabled'];
            $taxes = static::enabled()->where('is_default', true)->get($columns);
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
     * Custom scope to retrieve only enabled taxes.
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', 1);
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
