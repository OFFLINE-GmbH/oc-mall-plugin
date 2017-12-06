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

    public function addProduct(Product $product, int $quantity = 1, CustomFieldValue $value = null)
    {
        if ( ! $this->exists) {
            $this->save();
        }

        if ($product->stackable && $this->isInCart($product, $value)) {
            $product = $this->products->first(function (CartProduct $cartProduct) use ($product) {
                return $cartProduct->id === $product->id;
            });

            CartProduct::where('id', $product->id)->update(['quantity' => ++$product->quantity]);

            return $this->refresh();
        }

        $cartProduct             = new CartProduct();
        $cartProduct->cart_id    = $this->id;
        $cartProduct->product_id = $product->id;
        $cartProduct->quantity   = $quantity;
        $cartProduct->price      = $product->getOriginal('price');
        $cartProduct->save();

        if ($value) {
            $value->cart_product_id = $cartProduct->id;
            $value->save();
            $this->products->each(function (CartProduct $product) {
                $product->load('custom_field_values');
            });
        }

        $this->refresh();
    }

    public function isInCart(Product $product, CustomFieldValue $value = null): bool
    {
        $productIsInCart = $this->products->contains($product->id);
        // If there is no CustomFieldValue to compare we only have
        // to check if the product is in the cart.
        if ($value === null || $productIsInCart === false) {
            return $productIsInCart;
        }

        $hasCustomFieldOption = $value->custom_field_option_id !== null;

        $query = CustomFieldValue::where('custom_field_id', $value->custom_field_id);
        $query->whereHas('cart_product.cart', function ($query) {
            $query->where('id', 1);
        })->get();

        $query->when($hasCustomFieldOption, function ($query) use ($value) {
            $query->where('custom_field_option_id', $value->custom_field_option_id);
        })->when(! $hasCustomFieldOption, function ($query) use ($value) {
            $query->where('value', $value->value);
        });

        return $query->count() > 0;
    }

    public function setShippingMethod(ShippingMethod $method)
    {
        $this->shipping_method_id = $method->id;
    }

    /**
     * Reload all data and relationships.
     *
     * To make sure that we use the latest pivot data we can use this
     * method to reload all relationships. Laravel's caching can
     * sometimes be a bit to aggressive when it comes to pivot data
     * that has been updated by the user.
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
}
