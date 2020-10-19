<?php namespace OFFLINE\Mall\Models;

use Carbon\Carbon;
use Cookie;
use DB;
use Event;
use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
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

    protected $dates = ['deleted_at'];
    protected $with = ['products', 'products.data', 'discounts', 'shipping_method', 'customer'];
    public $rules = [];
    public $table = 'offline_mall_carts';
    public $hasMany = [
        'products' => [CartProduct::class, 'deleted' => true],
    ];
    public $belongsTo = [
        'shipping_method'  => ShippingMethod::class,
        'payment_method'   => PaymentMethod::class,
        'shipping_address' => [Address::class, 'localKey' => 'shipping_address_id', 'deleted' => true],
        'billing_address'  => [Address::class, 'localKey' => 'billing_address_id', 'deleted' => true],
        'customer'         => [Customer::class, 'deleted' => true],
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

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $cart) {
            // Make sure the selected shipping method is available for the new address(es).
            if ($cart->shipping_method_id !== null && $cart->isDirty('shipping_address_id')) {
                $availableMethods = ShippingMethod::getAvailableByCart($cart);
                if ( ! $availableMethods->pluck('id')->contains($cart->shipping_method_id)) {
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
    }

    public function setBillingAddress(Address $address)
    {
        $this->billing_address_id = $address->id;
    }

    public function setShippingAddress(Address $address)
    {
        $this->shipping_address_id = $address->id;
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
        return $this->products->count() > 0 && $this->products->every(function (CartProduct $product) {
            return $product->data->is_virtual;
        });
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
     * @param Product         $product
     * @param Variant         $variant
     * @param Collection|null $values
     *
     * @return bool
     */
    public function isInCart(Product $product, ?Variant $variant = null, ?Collection $values = null): bool
    {
        $productIsInCart = $this->products->contains(function (CartProduct $existing) use ($product, $variant) {
            $productIsInCart = $existing->product_id === $product->id;
            $variantIsInCart = $variant ? $existing->variant_id === $variant->id : true;

            return $productIsInCart && $variantIsInCart;
        });

        // If there is no CustomFieldValue to compare we only have
        // to check if the product is in the cart.
        if ($values === null || $values->count() === 0 || $productIsInCart === false) {
            return $productIsInCart;
        }

        foreach ($values as $value) {
            $hasCustomFieldOption = $value->custom_field_option_id !== null;

            $query = CustomFieldValue::where('custom_field_id', $value->custom_field_id);
            $query->whereHas('cart_product.cart', function ($query) {
                $query->where('id', $this->id);
            });

            $query->when($hasCustomFieldOption, function ($query) use ($value) {
                $query->where('custom_field_option_id', $value->custom_field_option_id);
            })->when(! $hasCustomFieldOption, function ($query) use ($value) {
                $query->where('value', $value->value);
            });
        }

        return $query->count() > 0;
    }

    /**
     * Cleanup of old data using OFFLINE.GDPR.
     *
     * @see https://github.com/OFFLINE-GmbH/oc-gdpr-plugin
     *
     * @param Carbon $deadline
     * @param int    $keepDays
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
     * @param int    $id
     * @param array  $price
     * @param string $name
     *
     * @example $cart->forceShippingPrice(1, ['EUR' => 200], 'Fee Zone 2');
     *
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
}
