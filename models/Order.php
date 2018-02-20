<?php namespace OFFLINE\Mall\Models;

use DB;
use Event;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\PaymentState\PaymentState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Classes\Traits\Price;
use RuntimeException;

/**
 * Model
 */
class Order extends Model
{
    use Validation;
    use SoftDelete;
    use Price;

    protected $dates = ['deleted_at'];

    public $rules = [
        'currency'                         => 'required',
        'shipping_address_same_as_billing' => 'required|boolean',
        'billing_address'                  => 'required',
        'lang'                             => 'required',
        'ip_address'                       => 'required',
        'customer_id'                      => 'required|exists:offline_mall_customers,id',
    ];

    public $jsonable = [
        'billing_address',
        'shipping_address',
        'custom_fields',
        'taxes',
        'discounts',
        'shipping',
        'payment_data',
    ];

    public $table = 'offline_mall_orders';

    public $hasMany = [
        'products' => OrderProduct::class,
    ];

    public $belongsTo = [
        'payment_method' => [PaymentMethod::class, 'deleted' => true],
        'order_state'    => [OrderState::class, 'deleted' => true],
        'customer'       => [Customer::class, 'deleted' => true],
    ];

    public $casts = [
        'shipping_address_same_as_billing' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $order) {
            if ( ! $order->order_number) {
                $order->setOrderNumber();
            }
        });
        static::updated(function (self $order) {
            if ($order->isDirty('order_state_id')) {
                Event::fire('mall.order.state.changed', [$order]);
            }
            if ($order->isDirty('tracking_url') || $order->isDirty('tracking_number')) {
                Event::fire('mall.order.tracking.changed', [$order]);
            }
            if ($order->isDirty('payment_state')) {
                Event::fire('mall.order.payment_state.changed', [$order]);
            }
        });
    }

    public static function fromCart(Cart $cart): self
    {
        $order = DB::transaction(function () use ($cart) {

            $initialOrderStatus = OrderState::where('flag', OrderState::FLAG_NEW)->first();
            if ( ! $initialOrderStatus) {
                throw new RuntimeException('You have to create an order state with the "new" flag before accepting orders!');
            }

            $order                                          = new static;
            $order->currency                                = 'CHF';
            $order->lang                                    = 'de';
            $order->shipping_address_same_as_billing        = $cart->shipping_address_same_as_billing;
            $order->billing_address                         = $cart->billing_address;
            $order->shipping_address                        = $cart->shipping_address;
            $order->shipping                                = $cart->totals->shippingTotal();
            $order->taxes                                   = $cart->totals->taxes();
            $order->discounts                               = $cart->totals->appliedDiscounts();
            $order->ip_address                              = request()->ip();
            $order->customer_id                             = 1;
            $order->payment_method_id                       = $cart->payment_method_id;
            $order->payment_state                           = PendingState::class;
            $order->order_state_id                          = $initialOrderStatus->id;
            $order->attributes['total_shipping_pre_taxes']  = $order->round($cart->totals->shippingTotal()->totalPreTaxes());
            $order->attributes['total_shipping_taxes']      = $order->round($cart->totals->shippingTotal()->totalTaxes());
            $order->attributes['total_shipping_post_taxes'] = $order->round($cart->totals->shippingTotal()->totalPostTaxes());
            $order->attributes['total_product_pre_taxes']   = $order->round($cart->totals->productPreTaxes());
            $order->attributes['total_product_taxes']       = $order->round($cart->totals->productTaxes());
            $order->attributes['total_product_post_taxes']  = $order->round($cart->totals->productPostTaxes());
            $order->attributes['total_pre_taxes']           = $order->round($cart->totals->totalPreTaxes());
            $order->attributes['total_taxes']               = $order->round($cart->totals->totalTaxes());
            $order->attributes['total_post_taxes']          = $order->round($cart->totals->totalPostTaxes());
            $order->total_weight                            = $order->round($cart->totals->weightTotal());
            $order->save();

            $cart->products->each(function (CartProduct $entry) use ($order) {
                $entry->moveToOrder($order);
            });

            $cart->delete(); // We can empty the cart once the order is created.

            return $order;
        });

        return $order;
    }

    protected function round($amount)
    {
        return round($amount);
    }

    /**
     * Sets the order number to the next higher value.
     */
    protected function setOrderNumber()
    {
        $numbers = DB::table($this->getTable())->selectRaw('max(cast(order_number as unsigned)) as max')->first();
        $start   = $numbers->max;

        if ($start === 0) {
            $start = 0; // @TODO: Add custom starting point for numbers
        }

        $this->order_number = $start + 1;
    }

    public function getPriceColumns(): array
    {
        return [
            'total_shipping_pre_taxes',
            'total_shipping_taxes',
            'total_shipping_post_taxes',
            'total_product_pre_taxes',
            'total_product_taxes',
            'total_product_post_taxes',
            'total_taxes',
            'total_post_taxes',
            'total_pre_taxes',
        ];
    }
}
