<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Carbon\Carbon;
use DB;
use Event;
use Exception;
use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Classes\Exceptions\InvalidDiscountException;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Classes\Traits\Cart\CartActions;
use OFFLINE\Mall\Classes\Traits\Cart\CartSession;
use OFFLINE\Mall\Classes\Traits\Cart\Discounts;
use OFFLINE\Mall\Classes\Traits\ShippingMethods;
use Session;

/**
 * @property TotalsCalculator totals
 */
class Cart extends Model
{
    use Validation;
    use SoftDelete;
    use CartSession;
    use CartActions;
    use Discounts;
    use ShippingMethods;

    public const FALLBACK_SHIPPING_COUNTRY_KEY = 'mall.fallback_shipping_country_id';

    public $rules = [];

    public $table = 'offline_mall_carts';

    public $hasMany = [
        'products' => [CartProduct::class, 'deleted' => true],
    ];

    public $belongsTo = [
        'shipping_method' => [
            ShippingMethod::class,
            'scope' => 'all',
        ],
        'payment_method' => [
            PaymentMethod::class,
            'scope' => 'all',
        ],
        'shipping_address' => [Address::class, 'localKey' => 'shipping_address_id', 'deleted' => true],
        'billing_address' => [Address::class, 'localKey' => 'billing_address_id', 'deleted' => true],
        'customer' => [Customer::class, 'deleted' => true],
    ];

    public $hasOne = [
        'wishlist' => Wishlist::class,
    ];

    public $belongsToMany = [
        'discounts' => [
            Discount::class,
            'table' => 'offline_mall_cart_discount',
        ],
    ];

    public $casts = [
        'shipping_address_same_as_billing' => 'boolean',
    ];

    public $fillable = ['session_id', 'customer_id'];

    /**
     * @var TotalsCalculator
     */
    public $totalsCached;

    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $cart) {
            // Make sure the selected shipping method is available for the new address(es).
            if ($cart->shipping_method_id !== null && $cart->isDirty('shipping_address_id')) {
                $availableMethods = ShippingMethod::getAvailableByCart($cart);

                if (!$availableMethods->pluck('id')->contains($cart->shipping_method_id)) {
                    $cart->shipping_method_id = ShippingMethod::getDefault()->id;
                }
            }
        });
    }

    public function setPaymentMethod($method)
    {
        if ($method instanceof PaymentMethod) {
            $method = $method->id;
        }

        $this->payment_method_id = $method;
        $this->save();
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer_id = $customer->id;

        $this->forgetFallbackShippingCountryId();
    }

    public function setBillingAddress(Address $address)
    {
        $this->billing_address_id = $address->id;
    }

    public function setShippingAddress(Address $address)
    {
        $this->shipping_address_id = $address->id;

        $this->forgetFallbackShippingCountryId();
    }

    public function getTotalsAttribute()
    {
        if ($this->totalsCached) {
            return $this->totalsCached;
        }

        return $this->totals();
    }

    /**
     * Check if this cart has only virtual products in it.
     * @return bool
     */
    public function getIsVirtualAttribute(): bool
    {
        return $this->products->count() > 0 && $this->products->every(fn (CartProduct $product) => $product->data->is_virtual);
    }

    public function getShippingAddressSameAsBillingAttribute(): bool
    {
        return $this->shipping_address_id === $this->billing_address_id;
    }

    public function totals(): TotalsCalculator
    {
        return $this->totalsCached = new TotalsCalculator(TotalsCalculatorInput::fromCart($this));
    }

    /**
     * Updates the quantity for one cart entry.
     * @param mixed $cartProductId
     */
    public function setQuantity($cartProductId, int $quantity)
    {
        $product = $this->products->find($cartProductId);

        if ($product) {
            $this->validateStock($product->item, $quantity, $product->id);

            $oldQuantity = $product->quantity;

            $product->quantity = $quantity;
            $product->save();

            Event::fire('mall.cart.product.quantityChanged', [$product, (int)$oldQuantity, (int)$quantity]);
        }
        $this->validateShippingMethod();
    }

    /**
     * Checks if a product with the same $value is already
     * in the cart.
     *
     * @param Product $product
     * @param Variant $variant
     * @param Collection|null $values
     *
     * @return CartProduct|null
     */
    public function getMatchingProductInCart(Product $product, ?Variant $variant = null, ?Collection $values = null): CartProduct|null
    {
        $productsInCart = $this->products->where(function (CartProduct $existing) use ($product, $variant) {
            $productIsInCart = $existing->product_id === $product->id;
            $variantIsInCart = $variant ? $existing->variant_id === $variant->id : true;

            return $productIsInCart && $variantIsInCart;
        });

        // If there are no CustomFieldValue items provided, only match
        // cart products that also have no custom field values.
        if ($values === null || $values->count() === 0) {
            return CartProduct::whereIn('id', $productsInCart->pluck('id'))
                ->whereDoesntHave('custom_field_values')
                ->first();
        }

        // Find the cart product that has exactly the provided custom field values (no more, no less).
        $ids = $productsInCart->pluck('id');

        $query = CartProduct::query()
            ->whereIn('id', $ids)
            ->select('offline_mall_cart_products.*')
            // Total number of custom field values attached to the cart product
            ->selectSub(function ($q) {
                $q->from('offline_mall_cart_custom_field_value')
                    ->selectRaw('count(*)')
                    ->whereColumn('offline_mall_cart_products.id', 'offline_mall_cart_custom_field_value.cart_product_id');
            }, 'custom_field_values_count')
            // Number of custom field values that match the provided set
            ->selectSub(function ($q) use ($values) {
                $q->from('offline_mall_cart_custom_field_value')
                    ->selectRaw('count(*)')
                    ->whereColumn('offline_mall_cart_products.id', 'offline_mall_cart_custom_field_value.cart_product_id')
                    ->where(function ($q) use ($values) {
                        foreach ($values as $value) {
                            $q->orWhere(function ($q) use ($value) {
                                $hasCustomFieldOption = $value->custom_field_option_id !== null;

                                $q->where('custom_field_id', $value->custom_field_id);

                                if ($hasCustomFieldOption) {
                                    $q->where('custom_field_option_id', $value->custom_field_option_id);
                                } else {
                                    $q->whereNull('custom_field_option_id')->where('value', $value->value);
                                }
                            });
                        }
                    });
            }, 'matching_field_values_count')
            ->having('custom_field_values_count', '=', $values->count())
            ->having('matching_field_values_count', '=', $values->count());

        return $query->first();
    }

    /**
     * Remove all products that are no longer published.
     * Returns all removed products.
     *
     * @return \October\Rain\Support\Collection
     * @throws Exception
     */
    public function removeUnpublishedProducts()
    {
        return $this->products->map(function (CartProduct $product) {
            if (!$product->item->published) {
                $product->delete();

                return $product;
            }

            return null;
        })->filter();
    }

    /**
     * This is the country ID that is used for carts that do not have
     * a shipping address yet.
     */
    public function getFallbackShippingCountryId()
    {
        return Session::get(self::FALLBACK_SHIPPING_COUNTRY_KEY);
    }

    public function setFallbackShippingCountryId($value)
    {
        if ($value) {
            Session::put(self::FALLBACK_SHIPPING_COUNTRY_KEY, $value);
        }
    }

    public function forgetFallbackShippingCountryId()
    {
        Session::forget(self::FALLBACK_SHIPPING_COUNTRY_KEY);
    }

    /**
     * Cleanup of old data using OFFLINE.GDPR.
     *
     * @see https://github.com/OFFLINE-GmbH/oc-gdpr-plugin
     *
     * @param Carbon $deadline
     * @param int $keepDays
     */
    public function gdprCleanup(Carbon $deadline, int $keepDays)
    {
        self::withTrashed()
            ->where('updated_at', '<', $deadline)
            ->get()
            ->each(function (Cart $cart) {
                DB::transaction(function () use ($cart) {
                    $cart->forceDelete();
                });
            });
    }

    /**
     * Enforce a fixed shipping price for a shipping method.
     *
     * The provided price will override the default price for the
     * current shipping method.
     * You can pass an optional name override as second parameter.
     * This is useful if you need a dynamic way to set shipping costs
     * based on an arbitrary other value.
     *
     * @param int $id
     * @param array $price
     * @param string $name
     *
     * @example $cart->forceShippingPrice(1, ['EUR' => 200], 'Fee Zone 2');
     */
    public function forceShippingPrice(int $id, array $price, string $name = '')
    {
        Session::put('mall.shipping.enforced.' . $id . '.price', $price);

        if ($name) {
            Session::put('mall.shipping.enforced.' . $id . '.name', $name);
        }
    }

    /**
     * Undo an enforced shipping price.
     */
    public function forgetForcedShippingPrice()
    {
        Session::forget('mall.shipping.enforced');
    }

    /**
     * Validate discounts and remove those that are no longer valid.
     * @return void
     */
    public function validateDiscounts()
    {
        if ($this->discounts->count() === 0) {
            return;
        }

        $discountApplier = new DiscountApplier(TotalsCalculatorInput::fromCart($this), $this->totals->totalPostTaxes());

        $invalidDiscounts = new Collection();
        $this->discounts->each(function (Discount $discount) use ($discountApplier, $invalidDiscounts) {
            if (!$discountApplier->discountCanBeApplied($discount)) {
                $this->discounts()->remove($discount);
                $invalidDiscounts->push($discount);
            }
        });

        if ($invalidDiscounts->count() > 0) {
            $this->save();

            throw new InvalidDiscountException($this, $invalidDiscounts);
        }
    }
}
