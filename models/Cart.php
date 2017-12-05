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

    public $belongsToMany = [
        'products' => [
            Product::class,
            'table'      => 'offline_mall_cart_product',
            'key'        => 'cart_id',
            'otherKey'   => 'product_id',
            'timestamps' => true,
            'pivot'      => ['id', 'quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
    ];

    public $belongsTo = [
        'shipping_method' => ShippingMethod::class,
    ];

    /**
     * When using pivot tables October's memory cache does
     * more good than harm so we disable it for this model.
     *
     * @var bool
     */
    public $duplicateCache = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function (Cart $cart) {
            if ( ! $cart->user_id) {
                $cart->session_id = str_random(100);
            }
        });
    }

    public function addProduct(Product $product, int $quantity = 1)
    {
        if ( ! $this->exists) {
            $this->save();
        }

        if ($product->stackable && $this->products->contains($product->id)) {
            $product = $this->products->find($product);

            $quantity = ++$product->pivot->quantity;
            $this->products()->newPivotStatement()
                 ->where('id', $product->pivot->id)
                 ->update(['quantity' => $quantity]);

            return $this->refresh();
        }

        $this->products()
             ->attach($product, [
                 'quantity' => $quantity,
                 'price'    => $product->getOriginal('price'),
             ]);

        $this->refresh();
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
