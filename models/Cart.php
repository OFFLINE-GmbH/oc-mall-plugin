<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;

class Cart extends Model
{
    use Validation;
    use SoftDelete;

    protected $dates = ['deleted_at'];
    protected $with = ['products'];

    public $rules = [
        'session_id' => 'required_if,session_id,NULL',
        'user_id'    => 'required_if,user_id,NULL',
    ];

    public $table = 'offline_mall_carts';

    public $hasMany = [
        'products' => [CartProduct::class, 'deleted' => true],
    ];

    public $belongsTo = [
        'shipping_method' => ShippingMethod::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (Cart $cart) {
            if ( ! $cart->user_id) {
                $cart->session_id = str_random(100);
            }
        });
    }

    public function setShippingMethod(ShippingMethod $method)
    {
        $this->shipping_method_id = $method->id;
    }

    /**
     * Adds a product to the cart.
     *
     * @param Product            $product
     * @param int|null           $quantity
     * @param CustomFieldValue[] $values
     */
    public function addProduct(Product $product, int $quantity = null, $values = null)
    {
        if ( ! $this->exists) {
            $this->save();
        }

        $quantity = $quantity ?? $product->quantity_default ?? 1;
        $values   = $this->normalizeArray($values);

        if ($product->stackable && $this->isInCart($product, $values)) {
            $cartProduct = $this->products->first(function (CartProduct $cartProduct) use ($product) {
                return $cartProduct->id === $product->id;
            });

            $newQuantity = $this->normalizeQuantity($cartProduct->quantity + $quantity, $product);

            CartProduct::where('id', $cartProduct->id)->update(['quantity' => $newQuantity]);

            return $this->refresh();
        }

        $quantity = $this->normalizeQuantity($quantity, $product);

        $cartProduct             = new CartProduct();
        $cartProduct->cart_id    = $this->id;
        $cartProduct->product_id = $product->id;
        $cartProduct->quantity   = $quantity;
        $cartProduct->price      = $product->priceIncludingCustomFieldValues($values);
        $cartProduct->save();

        foreach ($values as $value) {
            $value->cart_product_id = $cartProduct->id;
            $value->save();
        }

        $this->refresh();
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
        $productIsInCart = $this->products->contains($product->id);
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
     * Reload all data and relationships.
     *
     * To make sure that we use the latest  data we can use this
     * method to reload all relationships. Laravel's caching can
     * sometimes be a bit to aggressive when it comes to related data
     * that has been updated manualy by the user.
     *
     * @return void
     */
    public function refresh()
    {
        $relations = collect($this->relations)
            ->except('pivot')
            ->keys()
            ->toArray();

        $this->reload();
        $this->load($relations);
    }

    /**
     * Enforce min and max quantity values for a product.
     *
     * @return int
     */
    private function normalizeQuantity($quantity, Product $product): int
    {
        if ($product->quantity_min && $quantity < $product->quantity_min) {
            return $product->quantity_min;
        }
        if ($product->quantity_max && $quantity > $product->quantity_max) {
            return $product->quantity_max;
        }

        return $quantity;
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
