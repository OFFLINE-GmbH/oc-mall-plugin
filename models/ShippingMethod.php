<?php namespace OFFLINE\Mall\Models;

use Closure;
use Illuminate\Support\Facades\Session;
use Model;
use October\Rain\Database\Collection;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use Rainlab\Location\Models\Country as RainLabCountry;
use System\Models\File;

class ShippingMethod extends Model
{
    use Validation;
    use Sortable;
    use PriceAccessors {
        priceRelation as priceAccessorPriceRelation;
    }

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
    public $fillable = [
        'name',
        'description',
        'guaranteed_delivery_days',
        'price_includes_tax',
        'sort_order',
    ];
    public $morphMany = [
        'prices'                 => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
        'available_below_totals' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => "price_category_id is null and field = 'available_below_totals'",
        ],
        'available_above_totals' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => "price_category_id is null and field = 'available_above_totals'",
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

    /**
     * This method can be used when no shipping is required
     * for example when there are only virtual products in a cart.
     *
     * @return ShippingMethod
     */
    public static function noShippingRequired()
    {
        return new self([
            'name'        => trans('offline.mall::lang.shipping_method.not_required_name'),
            'description' => trans('offline.mall::lang.shipping_method.not_required_description'),
        ]);
    }

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

    public function getNameAttribute()
    {
        $enforcedKey = sprintf('mall.shipping.enforced.%s.name', $this->id);
        if ($this->useEnforcedValues() && $enforced = Session::get($enforcedKey)) {
            return $enforced;
        }

        return $this->attributes['name'] ?? '';
    }

    /**
     * Check if enforced shipping price/name should be used.
     * The values are ignored if a ShippingMethodSelector component
     * is present on the current page.
     *
     * @return bool
     */
    protected function useEnforcedValues()
    {
        // Never use enforced values in the backend.
        if (app()->runningInBackend() === true) {
            return false;
        }

        return true;
    }

    public static function getAvailableByCart(Cart $cart)
    {
        // Virtual carts cannot be shipped.
        if ($cart->is_virtual) {
            return collect([]);
        }

        $total = $cart->totals()->productPostTaxes();

        return self
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

    public static function getAvailableByWishlist(?Wishlist $wishlist)
    {
        if ( ! $wishlist) {
            return new Collection();
        }

        $total = $wishlist->totals()->productPostTaxes();

        $countryId = $wishlist->getCartCountryId();

        return self
            ::orderBy('sort_order')
            ->when($countryId, function ($q) use ($countryId) {
                $q->whereDoesntHave('countries')
                  ->orWhereHas('countries', function ($q) use ($countryId) {
                      $q->where('country_id', $countryId);
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

    protected function priceRelation(
        $currency = null,
        $relation = 'prices',
        ?Closure $filter = null
    ) {
        $checkEnforced = $relation === 'prices' && $this->useEnforcedValues();
        $enforcedKey   = sprintf('mall.shipping.enforced.%s.price', $this->id);

        if ($checkEnforced && $enforced = Session::get($enforcedKey, [])) {
            $currency = Currency::resolve($currency);
            $value    = array_get($enforced, $currency->code);
            $price    = new Price([
                'currency_id' => $currency->id,
                'price'       => $value,
            ]);

            return $price;
        }

        return $this->priceAccessorPriceRelation($currency, $relation, $filter);
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
