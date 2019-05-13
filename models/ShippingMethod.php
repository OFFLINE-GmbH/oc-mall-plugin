<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use System\Models\File;
use Rainlab\Location\Models\Country as RainLabCountry;

class ShippingMethod extends Model
{
    use Validation;
    use Sortable;
    use PriceAccessors;

    const MORPH_KEY = 'mall.shipping_method';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $with = ['prices'];
    public $hidden = ['created_at', 'updated_at', 'deleted_at'];
    public $translatable = [
        'name',
        'description',
    ];
    public $rules = [
        'name' => 'required',
    ];
    public $casts = [
        'price_includes_tax' => 'boolean',
    ];
    public $table = 'offline_mall_shipping_methods';
    public $appends = ['price_formatted'];
    public $morphMany = [
        'prices'                 => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
        'available_below_totals' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field = "available_below_totals"',
        ],
        'available_above_totals' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field = "available_above_totals"',
        ],
    ];
    public $hasMany = [
        'carts' => Cart::class,
        'rates' => ShippingMethodRate::class,
    ];
    public $attachOne = [
        'logo' => File::class,
    ];
    public $belongsToMany = [
        'taxes'     => [
            Tax::class,
            'table'    => 'offline_mall_shipping_method_tax',
            'key'      => 'shipping_method_id',
            'otherKey' => 'tax_id',
        ],
        'countries' => [
            RainLabCountry::class,
            'table'    => 'offline_mall_shipping_countries',
            'key'      => 'shipping_method_id',
            'otherKey' => 'country_id',
        ],
    ];

    public function afterDelete()
    {
        \DB::table('offline_mall_prices')
           ->where('priceable_type', self::MORPH_KEY)
           ->where('priceable_id', $this->id)
           ->delete();
    }

    public static function getDefault(): self
    {
        return ShippingMethod::first();
    }

    public function getPriceFormattedAttribute()
    {
        return $this->price()->string;
    }

    public static function getAvailableByCart(Cart $cart)
    {
        $total = $cart->totals()->productPostTaxes();

        return ShippingMethod
            ::orderBy('sort_order')
            ->when($cart->shipping_address, function ($q) use ($cart) {
                $q->whereDoesntHave('countries')
                  ->orWhereHas('countries', function ($q) use ($cart) {
                      $q->where('country_id', $cart->shipping_address->country_id);
                  });
            })
            ->get()
            ->filter(function (ShippingMethod $method) use ($total) {
                $below = $method->availableBelowTotal()->integer;
                $above = $method->availableAboveTotal()->integer;

                return ($below === null || $below > $total)
                    && ($above === null || $above <= $total);
            });
    }

    public function availableBelowTotal($currency = null)
    {
        return $this->price($currency, 'available_below_totals');
    }

    public function availableAboveTotal($currency = null)
    {
        return $this->price($currency, 'available_above_totals');
    }

    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();
        $this->prices->load('currency');
        unset($base['price']);
        $base['price'] = $this->prices->mapWithKeys(function ($price) {
            return [$price->currency->code => $price];
        });

        return $base;
    }
}
