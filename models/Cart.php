<?php namespace OFFLINE\Mall\Models;

use Carbon\Carbon;
use Cookie;
use DB;
use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use OFFLINE\Mall\Classes\Traits\Cart\CartActions;
use OFFLINE\Mall\Classes\Traits\Cart\CartSession;
use OFFLINE\Mall\Classes\Traits\Cart\Discounts;
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

    public function setShippingMethod(?ShippingMethod $method)
    {
        $this->shipping_method_id = $method ? $method->id : null;
        $this->save();
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

        return $this->totalsCached = new TotalsCalculator($this);
    }

    public function getShippingAddressSameAsBillingAttribute(): bool
    {
        return $this->shipping_address_id === $this->billing_address_id;
    }

    public function totals(): TotalsCalculator
    {
        if ($this->totalsCached) {
            return $this->totalsCached;
        }
        return $this->totalsCached = new TotalsCalculator($this);
    }

    /**
     * Updates the quantity for one cart entry.
     */
    public function setQuantity($cartProductId, int $quantity)
    {
        $product = $this->products->find($cartProductId);
        if ($product) {
            $this->validateStock($product->item, $quantity, $product->id);
            $product->quantity = $quantity;
            $product->save();
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
     * Makes sure that the selected shipping method
     * can still be applied to this cart.
     */
    public function validateShippingMethod()
    {
        if ( ! $this->shipping_method_id) {
            return true;
        }

        $available = ShippingMethod::getAvailableByCart($this);
        if ($available->pluck('id')->contains($this->shipping_method_id)) {
            return true;
        }

        if (count($available) > 0) {
            return $this->setShippingMethod($available->first());
        }

        return $this->setShippingMethod(null);
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
}
