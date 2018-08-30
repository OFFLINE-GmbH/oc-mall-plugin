<?php namespace OFFLINE\Mall\Models;

use DB;
use Event;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\JsonPrice;
use RainLab\Translate\Classes\Translator;
use RuntimeException;

class Order extends Model
{
    use Validation;
    use SoftDelete;
    use JsonPrice {
        useCurrency as fallbackCurrency;
    }
    use HashIds;

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
        'currency',
        'discounts',
        'shipping',
        'payment_data',
    ];
    public $table = 'offline_mall_orders';
    public $hasMany = [
        'products'     => OrderProduct::class,
        'payment_logs' => [PaymentLog::class, 'order' => 'created_at DESC'],
    ];
    public $belongsTo = [
        'payment_method' => [PaymentMethod::class, 'deleted' => true],
        'order_state'    => [OrderState::class, 'deleted' => true],
        'customer'       => [Customer::class, 'deleted' => true],
        'cart'           => [Cart::class, 'deleted' => true],
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

    public static function byCustomer(Customer $customer)
    {
        return static::where('customer_id', $customer->id);
    }

    public static function fromCart(Cart $cart): self
    {
        $order = DB::transaction(function () use ($cart) {

            $initialOrderStatus = OrderState::where('flag', OrderState::FLAG_NEW)->first();
            if ( ! $initialOrderStatus) {
                throw new RuntimeException('You have to create an order state with the "new" flag before accepting orders!');
            }

            if ($cart->products->count() < 1) {
                throw new ValidationException(['Your order is empty. Please add a product to the cart.']);
            }

            $order                                          = new static;
            $order->session_id                              = session()->getId();
            $order->currency                                = Currency::activeCurrency();
            $order->lang                                    = Translator::instance()->getLocale();
            $order->shipping_address_same_as_billing        = $cart->shipping_address_same_as_billing;
            $order->billing_address                         = $cart->billing_address;
            $order->shipping_address                        = $cart->shipping_address;
            $order->shipping                                = $cart->totals->shippingTotal();
            $order->taxes                                   = $cart->totals->taxes();
            $order->discounts                               = $cart->totals->appliedDiscounts();
            $order->ip_address                              = request()->ip();
            $order->customer_id                             = $cart->customer->id;
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

            $cart->updateDiscountUsageCount();

            $cart->delete(); // We can empty the cart once the order is created.

            return $order;
        });

        // Drop any saved payment information since the order has been
        // created successfully.
        session()->forget('mall.payment_method.data');

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
            $start = (int)GeneralSettings::get('order_start');
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

    protected function useCurrency()
    {
        if ($this->currency) {
            return new Currency($this->currency);
        }

        return $this->fallbackCurrency();
    }

    /**
     * Returns the amount of the order in the selected currency.
     * This is used in the PaymentProvider classes.
     *
     * @return float
     */
    public function getTotalInCurrencyAttribute()
    {
        $total = (int)$this->getOriginal('total_post_taxes');
        $total *= (float)$this->currency['rate'];

        return round_money($total, $this->currency['decimals']);
    }

    public function getPaymentStateLabelAttribute()
    {
        return $this->payment_state::label();
    }

    public function getOrderStateLabelAttribute()
    {
        return $this->order_state->name;
    }

    public function getShippingAddressStringAttribute()
    {
        return implode("\n", $this->shipping_address);
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_state === PaidState::class;
    }
}
