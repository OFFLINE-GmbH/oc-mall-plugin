<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Closure;
use DB;
use Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Model;
use October\Rain\Database\Collection;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Database\IsStates;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use Rainlab\Location\Models\Country as RainLabCountry;
use System\Models\File;

class ShippingMethod extends Model
{
    use IsStates;
    use PriceAccessors {
        priceRelation as priceAccessorPriceRelation;
    }
    use Sortable;
    use Validation;

    /**
     * Morph key as used on the respective relationships.
     * @var string
     */
    public const MORPH_KEY = 'mall.shipping_method';

    /**
     * Disable `is_default` handler on IsStates trait. Even if ShippingMethod uses a default value,
     * the current IsStates trait does not support multiple defaults, especially by using an
     * additional linking table (`offline_mall_shipping_country`).
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
    public $table = 'offline_mall_shipping_methods';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'          => 'required',
        'is_enabled'    => 'nullable|boolean',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'description',
        'guaranteed_delivery_days',
        'price_includes_tax',
        'sort_order',
        'is_default',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'price_includes_tax'    => 'boolean',
        'is_enabled'            => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<string>
     */
    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    public $appends = [
        'price_formatted',
    ];

    /**
     * The relations to eager load on every query.
     * @var array
     */
    public $with = [
        'prices',
    ];

    /**
     * The attachOne relationships of this model.
     * @var array
     */
    public $attachOne = [
        'logo' => File::class,
    ];

    /**
     * The belongsToMany relationships of this model.
     * @var array
     */
    public $belongsToMany = [
        'taxes'     => [
            Tax::class,
            'table'    => 'offline_mall_shipping_method_tax',
            'key'      => 'shipping_method_id',
            'otherKey' => 'tax_id',
        ],
        'discounts'     => [
            Discount::class,
            'table'    => 'offline_mall_shipping_method_discount',
            'key'      => 'shipping_method_id',
            'otherKey' => 'discount_id',
        ],
        'countries' => [
            RainLabCountry::class,
            'table'    => 'offline_mall_shipping_countries',
            'key'      => 'shipping_method_id',
            'otherKey' => 'country_id',
        ],
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'carts' => Cart::class,
        'rates' => ShippingMethodRate::class,
    ];

    /**
     * The morphMany relationships of this model.
     * @var array
     */
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

    /**
     * This method can be used when no shipping is required, for example when there are only virtual
     * products in a cart.
     * @return ShippingMethod
     */
    public static function noShippingRequired(): self
    {
        return new self([
            'name'        => trans('offline.mall::lang.shipping_method.not_required_name'),
            'description' => trans('offline.mall::lang.shipping_method.not_required_description'),
        ]);
    }

    /**
     * Get the first shipping method.
     * @return null|self
     */
    public static function getDefault(): ?self
    {
        return ShippingMethod::first();
    }

    /**
     * Get shipping methods by cart.
     * @param Cart $cart
     * @return Collection|self[]
     */
    public static function getAvailableByCart(Cart $cart)
    {
        if ($cart->is_virtual) {
            // Virtual carts cannot be shipped.
            return collect([]);
        } else {
            $total = $cart->totals()->productPostTaxes();
            $countryId = optional($cart->shipping_address)->country_id;
    
            // Use the Country ID from the post data, if availalb.e
            if (post('country_id')) {
                $countryId = post('country_id');
            }
    
            return self::getAvailability($countryId, $total, $cart, null);
        }
    }

    /**
     * Get shipping methods by wishlist.
     * @param null|Wishlist $wishlist
     * @return Collection|self[]
     */
    public static function getAvailableByWishlist(?Wishlist $wishlist = null)
    {
        if (!$wishlist) {
            return new Collection();
        } else {
            $total = $wishlist->totals()->productPostTaxes();
            $countryId = $wishlist->getCartCountryId();

            return self::getAvailability($countryId, $total, null, $wishlist);
        }
    }

    /**
     * Get shipping methods by details.
     *
     * @param int $countryId
     * @param int $total
     * @param null|Cart $cart
     * @param null|Wishlist $wishlist
     * @return Collection|self[]
     */
    public static function getAvailability($countryId, $total, $cart = null, $wishlist = null)
    {
        $availableShippingMethods = self::orderBy('sort_order')
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

        Event::fire('mall.shipping.methods.availability', [&$availableShippingMethods, $cart, $wishlist]);

        return $availableShippingMethods;
    }

    /**
     * Include all shipping methods, even disabled ones.
     * @param Builder $query
     * @return void
     */
    public function scopeAll(Builder $query)
    {
        return $query->withDisabled();
    }

    /**
     * JSON serialize class.
     */
    public function jsonSerialize(): mixed
    {
        $base = parent::jsonSerialize();
        $this->prices->load('currency');
        unset($base['price']);
        $base['price'] = $this->prices->mapWithKeys(fn ($price) => [$price->currency->code => $price]);

        return $base;
    }

    /**
     * Hook after model has been deleted.
     * @return void
     */
    public function afterDelete()
    {
        DB::table('offline_mall_prices')
            ->where('priceable_type', self::MORPH_KEY)
            ->where('priceable_id', $this->id)
            ->delete();
    }

    /**
     * Get actual / enforced prices by currency.
     * @return array
     */
    public function getActualPricesAttribute()
    {
        $prices = [];

        foreach (Currency::all() as $currency) {
            $prices[$currency->code] = $this->price($currency);
        }

        return $prices;
    }

    /**
     * Get formatted price attribute.
     * @return null|string
     */
    public function getPriceFormattedAttribute(): ?string
    {
        return $this->price()->string;
    }

    /**
     * Get name attribute.
     * @return null|string
     */
    public function getNameAttribute(): ?string
    {
        $enforcedKey = sprintf('mall.shipping.enforced.%s.name', $this->id);

        if ($this->useEnforcedValues() && $enforced = Session::get($enforcedKey)) {
            return $enforced;
        } else {
            return $this->getAttributeTranslated('name');
        }
    }
    
    /**
     * Get price by ???
     * @param mixed $currency
     * @return mixed
     */
    public function availableBelowTotal($currency = null)
    {
        return $this->price($currency, 'available_below_totals');
    }
    
    /**
     * Get price by ???
     * @param mixed $currency
     * @return mixed
     */
    public function availableAboveTotal($currency = null)
    {
        return $this->price($currency, 'available_above_totals');
    }

    /**
     * Check if enforced shipping price/name should be used.The values are ignored if a
     * ShippingMethodSelector component is present on the current page.
     * @return bool
     */
    protected function useEnforcedValues()
    {
        return app()->runningInBackend() !== true; // Never use enforced values in the backend.
    }

    /**
     * Price Relation
     * @param mixed $currency
     * @param string $relation
     * @param null|Closure $filter
     * @return mixed
     */
    protected function priceRelation($currency = null, $relation = 'prices', ?Closure $filter = null)
    {
        $checkEnforced = $relation === 'prices' && $this->useEnforcedValues();
        $enforcedKey   = sprintf('mall.shipping.enforced.%s.price', $this->id);

        if ($checkEnforced && $enforced = Session::get($enforcedKey, [])) {
            $currency = Currency::resolve($currency);
            $value    = array_get($enforced, $currency->code);

            return new Price([
                'currency_id' => $currency->id,
                'price'       => $value,
            ]);
        }

        return $this->priceAccessorPriceRelation($currency, $relation, $filter);
    }
}
