<?php namespace OFFLINE\Mall\Models;

use Cookie;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use RainLab\User\Models\User;
use Session;

class Cart extends Model
{
    use Validation;
    use SoftDelete;

    protected $dates = ['deleted_at'];
    protected $with = ['products', 'products.data', 'discounts', 'shipping_method', 'customer'];

    public $rules = [];

    public $table = 'offline_mall_carts';

    public $hasMany = [
        'products' => [CartProduct::class, 'deleted' => true],
    ];

    public $belongsTo = [
        'shipping_method'  => ShippingMethod::class,
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

    public $fillable = ['session_id'];

    /**
     * @var TotalsCalculator
     */
    public $totalsCached;

    public static function byUser(?User $user)
    {
        if ($user === null) {
            return self::bySession();
        }

        return self::firstOrCreate(['customer_id' => $user->customer->id]);
    }

    /**
     * Create a cart for an unregistered user. The cart id
     * is stored to the session and to a cookie. When the user
     * visits the website again we will try to fetch the id of an old
     * cart from the session or from the cookie.
     *
     * @return Cart
     */
    private static function bySession(): Cart
    {
        $sessionId = Session::get('cart_session_id') ?? Cookie::get('cart_session_id') ?? str_random(100);
        Cookie::queue('cart_session_id', $sessionId, 9e6);
        Session::put('cart_session_id', $sessionId);

        return self::firstOrCreate(['session_id' => $sessionId]);
    }

    public function setShippingMethod(ShippingMethod $method)
    {
        $this->shipping_method_id = $method->id;
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
        $this->shipping_address_id              = $address->id;
        $this->shipping_address_same_as_billing = false;
    }

    public function getTotalsAttribute()
    {
        if ($this->totalsCached) {
            return $this->totalsCached;
        }

        return $this->totalsCached = new TotalsCalculator($this);
    }

    public function totals(): TotalsCalculator
    {
        return $this->totalsCached = new TotalsCalculator($this);
    }

    /**
     * Adds a product to the cart.
     *
     * @param Product            $product
     * @param int|null           $quantity
     * @param CustomFieldValue[] $values
     *
     * @return void
     */
    public function addProduct(Product $product, int $quantity = null, $values = null)
    {
        if ( ! $this->exists) {
            $this->save();
        }

        $quantity = $quantity ?? $product->quantity_default ?? 1;
        $values   = $this->normalizeArray($values);

        if ($product->stackable && $this->isInCart($product, $values)) {
            $cartEntry = $this->products->first(function (CartProduct $cartProduct) use ($product) {
                return $cartProduct->product_id === $product->id;
            });

            $newQuantity = $product->normalizeQuantity($cartEntry->quantity + $quantity, $product);

            CartProduct::where('id', $cartEntry->id)->update(['quantity' => $newQuantity]);

            return $this->load('products');
        }

        $quantity = $product->normalizeQuantity($quantity);

        $cartEntry             = new CartProduct();
        $cartEntry->cart_id    = $this->id;
        $cartEntry->product_id = $product->id;
        $cartEntry->quantity   = $quantity;
        $cartEntry->price      = $product->priceIncludingCustomFieldValues($values);

        $this->products()->save($cartEntry);
        $this->load('products');

        $cartEntry->custom_field_values()->saveMany($values);
    }

    /**
     * Apply a discount to this cart.
     *
     * @param Discount $discount
     *
     * @throws \October\Rain\Exception\ValidationException
     * @throws ValidationException
     */
    public function applyDiscount(Discount $discount)
    {
        $uniqueDiscountTypes = ['alternate_price', 'shipping'];

        if (in_array($discount->type, $uniqueDiscountTypes)
            && $this->discounts->where('type', $discount->type)->count() > 0) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.' . $discount->type)]);
        }

        if ($this->discounts->contains($discount)) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.duplicate')]);
        }

        $this->discounts()->save($discount);
    }

    /**
     * Checks if a product with the same $value is already
     * in the cart.
     *
     * @param Product            $product
     * @param CustomFieldValue[] $values
     *
     * @return bool
     */
    public function isInCart(Product $product, array $values = []): bool
    {
        $productIsInCart = $this->products->contains(function (CartProduct $existing) use ($product) {
            return $existing->product_id === $product->id;
        });
        // If there is no CustomFieldValue to compare we only have
        // to check if the product is in the cart.
        if (count($values) === 0 || $productIsInCart === false) {
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
     * Makes sure $value is an array and removes all null values.
     */
    protected function normalizeArray($values): array
    {
        if ( ! is_array($values)) {
            $values = [$values];
        }

        return array_filter($values);
    }
}
