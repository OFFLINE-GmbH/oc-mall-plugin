<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Model;
use October\Rain\Database\Traits\Validation;
use Rainlab\Location\Models\Country as RainLabCountry;

class Tax extends Model
{
    use Validation;

    public const DEFAULT_TAX_CACHE_KEY = 'mall.tax.default';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
    ];
    public $rules = [
        'name'       => 'required',
        'percentage' => 'numeric|min:0|max:100',
    ];
    public $fillable = [
        'name',
        'percentage',
    ];
    public $table = 'offline_mall_taxes';
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
            'conditions' => 'is_enabled = 1',
        ],
    ];

    public function getPercentageDecimalAttribute()
    {
        return (float)$this->percentage / 100;
    }

    public function beforeSave()
    {
        // Enforce a single default tax.
        if ($this->is_default) {
            DB::table($this->table)->where('id', '<>', $this->id)->update(['is_default' => false]);
        }
    }

    public function afterSave()
    {
        Cache::forget(self::DEFAULT_TAX_CACHE_KEY);
    }

    /**
     * Returns the default tax.
     *
     * @return tax
     */
    public static function defaultTax()
    {
        $tax = Cache::rememberForever(static::DEFAULT_TAX_CACHE_KEY, function () {
            $tax = static::orderBy('is_default', 'DESC')->first();

            return $tax->toArray();
        });

        static::guardMissingDefaultTax($tax);

        return (new Tax)->newFromBuilder($tax);
    }

    protected static function guardMissingDefaultTax($tax)
    {
        if ( ! $tax) {
            throw new RuntimeException(
                '[mall] Please configure at least one tax via the backend settings.'
            );
        }
    }
}
