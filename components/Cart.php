<?php namespace OFFLINE\Mall\Components;

use Auth;
use Flash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\ShippingMethod;
use Request;
use Session;

/**
 * The Cart component displays a user's cart.
 */
class Cart extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var CartModel
     */
    public $cart;
    /**
     * Default minimum quantity.
     *
     * @var int
     */
    public $defaultMinQuantity = 1;
    /**
     * Default maximum quantity.
     *
     * @var int
     */
    public $defaultMaxQuantity = 100;
    /**
     * Display the DiscountApplier component.
     *
     * @var bool
     */
    public $showDiscountApplier = true;
    /**
     * Show the shipping information in the cart.
     *
     * @var bool
     */
    public $showShipping = true;
    /**
     * Display a tax summary at the end of the cart.
     *
     * @var bool
     */
    public $showTaxes = true;
    /**
     * Display a proceed to checkout button.
     *
     * @var bool
     */
    public $showProceedToCheckoutButton = false;
    /**
     * The name of the product detail page.
     *
     * @var  string
     */
    public $productPage;
    /**
     * The name of the checkout page.
     *
     * @var  string
     */
    public $checkoutPage;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.cart.details.name',
            'description' => 'offline.mall::lang.components.cart.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'showDiscountApplier' => [
                'type'    => 'checkbox',
                'title'   => 'offline.mall::lang.components.cart.properties.showDiscountApplier.title',
                'default' => 1,
            ],
            'showTaxes'           => [
                'type'    => 'checkbox',
                'title'   => 'offline.mall::lang.components.cart.properties.showTaxes.name',
                'default' => 1,
            ],
            'showShipping' => [
                'type'    => 'checkbox',
                'title'   => 'offline.mall::lang.components.cart.properties.showShipping.title',
                'default' => 1,
            ],
            'showProceedToCheckoutButton' => [
                'type'    => 'checkbox',
                'title'   => 'offline.mall::lang.components.cart.properties.showProceedToCheckoutButton.title',
                'default' => 0,
            ],
        ];
    }

    /**
     * The component is initialized.
     *
     * Adds the DiscountApplier component as child component.
     *
     * @return void
     */
    public function init()
    {
        $this->addComponent(DiscountApplier::class, 'discountApplier', []);
    }

    /**
     * The component is executed.
     *
     * @return void
     */
    public function onRun()
    {
        $this->setData();
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    public function setData()
    {
        $cart = CartModel::byUser(Auth::getUser());
        $cart->load(['products', 'products.custom_field_values', 'discounts']);
        if ($cart->shipping_method_id === null) {
            $cart->setShippingMethod(ShippingMethod::getDefault());
        }
        $cart->validateShippingMethod();

        $this->setVar('cart', $cart);
        $this->setVar('productPage', GeneralSettings::get('product_page'));
        $this->setVar('checkoutPage', GeneralSettings::get('checkout_page'));
        $this->setVar('showDiscountApplier', $this->property('showDiscountApplier'));
        $this->setVar('showShipping', $this->property('showShipping'));
        $this->setVar('showTaxes', $this->property('showTaxes'));
        $this->setVar('showProceedToCheckoutButton', $this->property('showProceedToCheckoutButton'));
    }

    /**
     * The user updated the quantity of a specific cart item.
     *
     * @return void
     */
    public function onUpdateQuantity()
    {
        $id = $this->decode(input('id'));

        $cart    = CartModel::byUser(Auth::getUser());
        $product = $this->getProductFromCart($cart, $id);

        try {
            $cart->setQuantity($product->id, (int)input('quantity'));
        } catch (OutOfStockException $e) {
            Flash::error(trans('offline.mall::lang.common.out_of_stock', ['quantity' => $e->product->stock]));

            return;
        } finally {
            $this->setData();
        }
    }

    /**
     * The user removed an item from the cart.
     *
     * @return array
     */
    public function onRemoveProduct()
    {
        $id = $this->decode(input('id'));

        $cart = CartModel::byUser(Auth::getUser());

        $product = $this->getProductFromCart($cart, $id);

        $cart->removeProduct($product);

        $this->setData();

        return [
            'item'     => $this->dataLayerArray($product->product, $product->variant),
            'quantity' => $product->quantity,
            'new_items_count' => optional($cart->products)->count() ?? 0,
            'new_items_quantity' => optional($cart->products)->sum('quantity') ?? 0,
        ];
    }

    /**
     * Fetch the item from the user's cart.
     *
     * This fails if an item is modified that is not in the
     * currently logged in user's cart.
     *
     * @param CartModel $cart
     * @param mixed     $id
     *
     * @return mixed
     * @throws ModelNotFoundException
     */
    protected function getProductFromCart(CartModel $cart, $id)
    {
        return CartProduct
            ::whereHas('cart', function ($query) use ($cart) {
                $query->where('id', $cart->id);
            })
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * Return the dataLayer representation of an item.
     *
     * @param null $product
     * @param null $variant
     *
     * @return array
     */
    private function dataLayerArray($product = null, $variant = null)
    {
        $item = $variant ?? $product;

        return [
            'id'       => $item->prefixedId,
            'name'     => $product->name,
            'price'    => $item->price()->decimal,
            'brand'    => optional($item->brand)->name,
            'category' => optional($item->categories->first())->name,
            'variant'  => optional($variant)->name,
        ];
    }
}
