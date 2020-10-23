<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Contracts\Encryption\DecryptException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentRedirector;
use OFFLINE\Mall\Classes\Payments\PaymentService;
use OFFLINE\Mall\Components\Cart as CartComponent;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Redirect;
use Request;
use Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The Checkout component orchestrates the checkout process.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Checkout extends MallComponent
{
    /**
     * The user's cart.
     *
     * @var Cart
     */
    public $cart;
    /**
     * The error massage received from the PaymentProvider.
     *
     * @var string
     */
    public $paymentError;
    /**
     * The currently active step.
     *
     * @var string
     */
    public $step;
    /**
     * The order that was created during checkout.
     *
     * @var Order
     */
    public $order;
    /**
     * The name of the my account page.
     *
     * @var string
     */
    public $accountPage;
    /**
     * Google Tag Manager dataLayer code.
     *
     * @var array
     */
    public $dataLayer;
    /**
     * Backend setting whether shipping should be before payment.
     *
     * @var bool
     */
    public $shippingSelectionBeforePayment = false;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.checkout.details.name',
            'description' => 'offline.mall::lang.components.checkout.details.description',
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
            'step' => [
                'type' => 'dropdown',
                'name' => 'offline.mall::lang.components.checkout.properties.step.name',
            ],
        ];
    }

    /**
     * Options array for the step dropdown.
     *
     * @return array
     */
    public function getStepOptions()
    {
        return [
            'payment'   => trans('offline.mall::lang.components.checkout.steps.payment'),
            'shipping'  => trans('offline.mall::lang.components.checkout.steps.shipping'),
            'confirm'   => trans('offline.mall::lang.components.checkout.steps.confirm'),
            'failed'    => trans('offline.mall::lang.components.checkout.steps.failed'),
            'cancelled' => trans('offline.mall::lang.components.checkout.steps.cancelled'),
            'done'      => trans('offline.mall::lang.components.checkout.steps.done'),
        ];
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
        $this->addComponent(CartComponent::class, 'cart', ['showDiscountApplier' => false]);

        if ($this->param('step') === 'confirm') {
            $this->addComponent(AddressSelector::class, 'billingAddressSelector', ['type' => 'billing']);
            $this->addComponent(AddressSelector::class, 'shippingAddressSelector', ['type' => 'shipping']);
        }

        if ($this->param('step') === 'shipping') {
            $this->addComponent(
                ShippingMethodSelector::class,
                'shippingMethodSelector',
                ['skipIfOnlyOneAvailable' => true]
            );
        }

        if ($this->param('step') === 'payment') {
            $this->addComponent(PaymentMethodSelector::class, 'paymentMethodSelector', []);
        }

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

        $paymentMethod = PaymentMethod::find($cart->payment_method_id);
        if ( ! $paymentMethod) {
            $paymentMethod = PaymentMethod::getDefault();
            $cart->setPaymentMethod($paymentMethod);
        }

        $this->setVar('paymentMethod', $paymentMethod);
        $this->setVar('step', $this->property('step'));
        $this->setVar('accountPage', GeneralSettings::get('account_page'));
        $this->setVar(
            'shippingSelectionBeforePayment',
            GeneralSettings::get('shipping_selection_before_payment', false)
        );

        if ($orderId = request()->get('order')) {
            $orderId = $this->decode($orderId);
            $this->setVar('order', Order::byCustomer($user->customer)->find($orderId));
        }

        $this->setVar('dataLayer', $this->handleDataLayer());
    }

    /**
     * The component is executed.
     *
     * @return RedirectResponse|void
     * @throws \Cms\Classes\CmsException
     */
    public function onRun()
    {
        // An off-site payment has been completed
        if ($type = request()->input('return')) {
            return $this->handleOffSiteReturn($type);
        }

        // If no step is provided or the step is invalid, redirect the user to
        // the payment method selection screen.
        $step = $this->property('step');
        if ( ! $step || ! array_key_exists($step, $this->getStepOptions())) {
            $url = $this->stepUrl($this->shippingSelectionBeforePayment ? 'shipping' : 'payment');

            return redirect()->to($url);
        }

        // If an order has been created but something failed we can fetch the paymentError
        // from the order's payment logs.
        if ($step === 'failed' && $this->order) {
            $this->paymentError = optional($this->order->payment_logs->first())->message ?? 'Unknown error';
        }
    }

    /**
     * Handle the checkout process.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws ValidationException
     * @throws \Cms\Classes\CmsException
     */
    public function onCheckout()
    {
        $this->setData();

        if ($this->cart->payment_method_id === null) {
            throw new ValidationException(
                [trans('offline.mall::lang.components.checkout.errors.missing_settings')]
            );
        }

        // Safely decrypt and decode any payment data. Fall back to an empty array if something goes wrong.
        try {
            $paymentData = json_decode(decrypt(session()->get('mall.payment_method.data')), true);
        } catch (DecryptException $e) {
            $paymentData = [];
        }

        $paymentMethod = PaymentMethod::findOrFail($this->cart->payment_method_id);

        // Grab the PaymentGateway from the Service Container.
        $gateway = app(PaymentGateway::class);
        $gateway->init($paymentMethod, $paymentData);

        // Create the order first.
        $order = DB::transaction(function () {
            return Order::fromCart($this->cart);
        });

        // If the order was created successfully proceed with the payment.
        $paymentService = new PaymentService(
            $gateway,
            $order,
            $this->page->page->fileName
        );

        return $paymentService->process();
    }

    /**
     * The user was redirected back to the store from an
     * external payment service.
     *
     * @param string $type
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Cms\Classes\CmsException
     */
    protected function handleOffSiteReturn($type)
    {
        return (new PaymentRedirector($this->page->page->fileName))->handleOffSiteReturn($type);
    }

    /**
     * Return the URL for a specific checkout step.
     *
     * @param       $step
     * @param array $params
     *
     * @return string
     */
    public function stepUrl($step, $params = [])
    {
        $via = false;
        if (isset($params['via'])) {
            $via = array_pull($params, 'via');
        }

        $url = $this->controller->pageUrl(
            $this->page->page->fileName,
            array_merge($params, ['step' => $step])
        );

        if ( ! $via) {
            return $url;
        }

        return $url . '?' . http_build_query(['via' => $via]);
    }

    /**
     * Generate Google Tag Manager dataLayer code.
     */
    private function handleDataLayer()
    {
        $isCheckout = request()->get('flow') === 'checkout';

        if ( ! $this->page->layout->hasComponent('enhancedEcommerceAnalytics')) {
            return;
        }

        $useModel = $this->step === 'done' ? $this->order : $this->cart;

        $data = [
            'event'     => 'checkout',
            'ecommerce' => [
                'products' => $useModel->products->map(function ($item, $index) {
                    return $this->getDataLayerProductArray($item);
                }),
                'checkout' => [
                    'actionField' => [],
                ],
            ],
        ];

        if ($this->step === 'confirm') {
            $data['ecommerce']['checkout']['actionField'] = ['step' => 3];
        }

        if ($this->step === 'done') {
            // The "done" step should only count for the initial Checkout flow, not
            // later payments that are also redirected to this page.
            if ($isCheckout === false) {
                return [];
            }

            unset($data['event'], $data['ecommerce']['checkout']);

            $coupon                        = $this->getDataLayerCoupon();
            $data['ecommerce']['purchase'] = [
                'actionField' => [
                    'id'          => $this->order->hash_id,
                    'affiliation' => 'OFFLINE Mall',
                    'revenue'     => $this->order->total_post_taxes,
                    'tax'         => $this->order->total_taxes,
                    'shipping'    => $this->order->total_shipping_post_taxes,
                    'coupon'      => $coupon,
                ],
            ];
        }

        return $data;
    }

    protected function getDataLayerProductArray($item)
    {
        $name    = $item->product->name;
        $variant = optional($item->variant)->name;
        $price   = $item->total_post_taxes ?? $item->price()->integer;

        return [
            'id'       => $item->prefixedId,
            'name'     => $name,
            'price'    => (string)round($price / 100, 2),
            'brand'    => optional($item->product->brand)->name ?? array_get($item->brand, 'name'),
            'category' => $item->product->categories->first()->name,
            'variant'  => $variant,
            'quantity' => $item->quantity,
        ];
    }

    protected function getDataLayerCoupon()
    {
        $coupon  = null;
        $coupons = $this->order->discounts ?? [];

        if (count($coupons)) {
            $coupon = implode(',', array_map(function ($coupon) {
                return array_get($coupon, 'code');
            }, $coupons));
        }

        return $coupon;
    }
}
