<?php

namespace OFFLINE\Mall\Classes\Totals;


use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;

class TotalsCalculatorInput
{
    /**
     * @var Collection<Product>
     */
    public $products;
    /**
     * @var ShippingMethod
     */
    public $shipping_method;
    /**
     * @var Collection<Discount>
     */
    public $discounts;
    /**
     * @var PaymentMethod
     */
    public $payment_method;
    /**
     * @var int
     */
    public $shipping_country_id;
}