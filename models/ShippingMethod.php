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
    public $translatable = [
        'name',
        'description',
    ];
    public $rules = [
        'name' => 'required',
    ];
    public $table = 'offline_mall_shipping_methods';
    public $appends = ['price_formatted'];
    public $morphMany = [
        'prices'                => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
        'available_below_total' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field = "available_below_total"',
        ],
        'available_above_total' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field = "available_above_total"',
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

    public static function getDefault(): self
    {
        return ShippingMethod::first();
    }

    public function getPriceFormattedAttribute()
    {
        return $this->priceInCurrencyFormatted();
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
                $below = $method->availableBelowTotalInCurrencyInteger();
                $above = $method->availableAboveTotalInCurrencyInteger();

                return ($below === null || $below > $total)
                    && ($above === null || $above <= $total);
            });
    }

    public function availableBelowTotalInCurrencyInteger()
    {
        return $this->priceInCurrencyInteger(null, 'available_below_total');
    }

    public function availableAboveTotalInCurrencyInteger()
    {
        return $this->priceInCurrencyInteger(null, 'available_above_total');
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

