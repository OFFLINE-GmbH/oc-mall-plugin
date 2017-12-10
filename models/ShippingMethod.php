<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

/**
 * Model
 */
class ShippingMethod extends Model
{
    use Validation;
    use Sortable;
    use Price;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'name',
    ];

    public $rules = [
        'name'  => 'required',
        'price' => 'required|regex:/\d+([\.,]\d+)?/i',
    ];

    public $table = 'offline_mall_shipping_methods';

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

    public static function getAvailableByCart(Cart $cart)
    {
        $total = $cart->totals()->productPostTaxes();

        return ShippingMethod::orderBy('sort_order')
                             ->where(function ($q) use ($total) {
                                 $q->where('available_below_total', '>', $total)
                                   ->orWhereNull('available_below_total');
                             })
                             ->where(function ($q) use ($total) {
                                 $q->where('available_above_total', '<=', $total)
                                   ->orWhereNull('available_above_total');
                             })
                             ->when($cart->shipping_address, function ($q) use ($cart) {
                                 $q->whereDoesntHave('countries')
                                   ->orWhereHas('countries', function ($q) use ($cart) {
                                       $q->where('country_id', $cart->shipping_address->country_id);
                                   });
                             })
                             ->get();
    }

    public function getPriceColumns()
    {
        return ['price', 'available_below_total', 'available_above_total'];
    }
}
