<?php namespace OFFLINE\Mall\Models;

use Cookie;
use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Collection;
use OFFLINE\Mall\Classes\Traits\Cart\CartItemPriceAccessors;
use OFFLINE\Mall\Classes\Traits\HashIds;
use RainLab\User\Facades\Auth;
use Session;

class WishlistItem extends Model
{
    use Validation;
    use CartItemPriceAccessors;
    use HashIds;

    public $table = 'offline_mall_wishlist_items';
    public $rules = [
        'product_id'  => 'required|exists:offline_mall_products,id',
        'wishlist_id' => 'required|exists:offline_mall_wishlists,id',
        'variant_id'  => 'nullable|exists:offline_mall_product_variants,id',
    ];
    public $belongsTo = [
        'wishlist' => Wishlist::class,
        'product'  => [Product::class, 'deleted' => true],
        'variant'  => [Variant::class, 'deleted' => true],
        'data'     => [Product::class, 'key' => 'product_id'],
    ];
    public $casts = [
        'id'          => 'integer',
        'product_id'  => 'integer',
        'variant_id'  => 'integer',
        'wishlist_id' => 'integer',
        'quantity'    => 'integer',
    ];
    public $fillable = [
        'product_id',
        'variant_id',
        'wishlist_id',
        'quantity',
    ];

    public function getItemAttribute()
    {
        return $this->variant ?? $this->product;
    }

    public function price()
    {
        return $this->item->price();
    }

    public function getCartCountryId()
    {
        return $this->wishlist->getCartCountryId();
    }
}
