<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

class ShippingMethod extends Model
{
    use Validation;
    use Sortable;
    use Price;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $jsonable = ['price', 'available_below_total', 'available_above_total'];
    public $translatable = [
        'name',
        'description',
    ];
    public $rules = [
        'name'  => 'required',
        'price' => 'required',
    ];
    public $table = 'offline_mall_shipping_methods';
    public $appends = ['price_formatted'];
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
            Country::class,
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

        return ShippingMethod::orderBy('sort_order')
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

    public function getPriceColumns()
    {
        return ['price', 'available_below_total', 'available_above_total'];
    }
}
