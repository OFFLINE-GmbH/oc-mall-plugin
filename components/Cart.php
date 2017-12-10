<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Product;

class Cart extends ComponentBase
{
    use SetVars;

    public $cart;
    public $defaultMinQuantity = 1;
    public $defaultMaxQuantity = 100;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.cart.details.name',
            'description' => 'offline.mall::lang.components.cart.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onUpdateQuantity()
    {
        $cart    = CartModel::byUser(Auth::getUser());
        $product = CartProduct
            ::whereHas('cart', function ($query) use ($cart) {
                $query->where('id', $cart->id);
            })
            ->where('id', (int)input('id'))
            ->firstOrFail();

        $product->quantity = (int)input('quantity');
        $product->save();

        $this->setData();
    }

    protected function setData()
    {
        $this->setVar('cart', CartModel::byUser(Auth::getUser()));
    }
}
