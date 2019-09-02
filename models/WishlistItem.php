<?php namespace OFFLINE\Mall\Models;

use Cookie;
use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\Validation;
use RainLab\User\Models\User;
use Session;

/**
 * Model
 */
class Wishlist extends Model
{
    use Validation;

    public $table = 'offline_mall_wishlists';
    public $rules = [
        'name'        => 'required',
        'session_id'  => 'required_without:customer_id',
        'customer_id' => 'required_without:session_id,exists:offline_mall_customers',
        'cart_id'     => 'required:exists:offline_mall_carts',
    ];
    public $hasMany = [
        'items' => WishlistItem::class,
    ];

    /**
     * Return all wishlists for the currently logged in user or
     * the currently active user session.
     */
    public static function byUser(?User $user): Collection
    {
        $sessionId = static::getSessionId();

        return self::where('session_id', $sessionId)
                   ->when($user && $user->customer, function ($q) use ($user) {
                       $q->orWhere('customer_id', $user->customer->id);
                   })
                   ->orderBy('created_at')
                   ->get();
    }

    /**
     * Generate a unique wishlist session id.
     *
     * @return string
     */
    public static function getSessionId(): string
    {
        $sessionId = Session::get('wishlist_session_id') ?? Cookie::get('wishlist_session_id') ?? str_random(100);
        Cookie::queue('wishlist_session_id', $sessionId, 9e6);
        Session::put('wishlist_session_id', $sessionId);

        return $sessionId;
    }
}
