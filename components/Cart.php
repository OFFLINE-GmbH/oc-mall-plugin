<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\CartProduct;
use Request;
use Session;

class Cart extends ComponentBase
{
    use SetVars;
    use HashIds;

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
        $this->addJs('assets/pubsub.js');
        $this->setData();
    }

    public function onUpdateQuantity()
    {
        $id = $this->decode(input('id'));

        // Make sure the product is actually in the logged
        // in user's shopping cart.
        $cart    = CartModel::byUser(Auth::getUser());
        $product = CartProduct
            ::whereHas('cart', function ($query) use ($cart) {
                $query->where('id', $cart->id);
            })
            ->where('id', $id)
            ->firstOrFail();

        $cart->setQuantity($product->id, (int)input('quantity'));

        $this->setData();
    }

    public function onRemoveProduct()
    {
        $id = $this->decode(input('id'));

        // Make sure the product is actually in the logged
        // in user's shopping cart.
        $cart    = CartModel::byUser(Auth::getUser());
        $product = CartProduct
            ::whereHas('cart', function ($query) use ($cart) {
                $query->where('id', $cart->id);
            })
            ->where('id', $id)
            ->firstOrFail();

        $cart->removeProduct($product);

        $this->setData();
    }

    protected function setData()
    {
        $cart = CartModel::byUser(Auth::getUser());
        $cart->load(['products', 'products.custom_field_values']);
        $this->setVar('cart', $cart);
    }
}
