<?php namespace OFFLINE\Mall\Components;

use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\PaymentMethod;

/**
 * The QuickCheckout component provides a checkout process on a single page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuickCheckout extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var Cart
     */
    public $cart;
    /**
     * The currently selected payment method.
     *
     * @var PaymentMethod
     */
    public $paymentMethod;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.quickCheckout.details.name',
            'description' => 'offline.mall::lang.components.quickCheckout.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * The component is initialized.
     *
     * All child components get added.
     *
     * @return void
     */
    public function init()
    {
        $this->setData();
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $user = Auth::getUser();
        if ( ! $user) {
            return;
        }

        $cart = Cart::byUser($user);
        if ( ! $cart->payment_method_id) {
            $cart->setPaymentMethod(PaymentMethod::getDefault());
        }
        $this->setVar('cart', $cart);
        $this->setVar('paymentMethod', PaymentMethod::find($cart->payment_method_id) ?? PaymentMethod::getDefault());

        $this->setVar('dataLayer', $this->handleDataLayer());
    }

    /**
     * Generate Google Tag Manager dataLayer code.
     */
    private function handleDataLayer()
    {
        if ( ! $this->page->layout->hasComponent('enhancedEcommerceAnalytics')) {
            return;
        }

        $useModel = $this->step === 'done' ? $this->order : $this->cart;
        $coupon   = $this->getDataLayerCoupon();

        $data = [
            'ecommerce' => [
                'products' => $useModel->products->map(function ($item, $index) {
                    return $this->getDataLayerProductArray($item);
                }),
                'purchase' => [
                    'actionField' => [
                        'id'          => $this->order->hash_id,
                        'affiliation' => 'OFFLINE Mall',
                        'revenue'     => $this->order->total_post_taxes,
                        'tax'         => $this->order->total_taxes,
                        'shipping'    => $this->order->total_shipping_post_taxes,
                        'coupon'      => $coupon,
                    ],
                ],
            ],
        ];

        return $data;
    }
}
