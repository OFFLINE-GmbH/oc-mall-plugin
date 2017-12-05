<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;

class Cart extends Model
{
    use Validation;
    use SoftDelete;

    protected $dates = ['deleted_at'];

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
            'pivot'      => ['quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
    ];

    public $belongsTo = [
        'shipping_method' => ShippingMethod::class
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

    public function addProduct(Product $product, int $quantity = 1)
    {
        $this->products()
             ->attach($product, ['quantity' => $quantity, 'price' => $product->getOriginal('price')]);
    }

    public function setShippingMethod(ShippingMethod $method)
    {
        $this->shipping_method_id = $method->id;
    }
}
