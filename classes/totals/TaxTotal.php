<?php

namespace OFFLINE\Mall\Classes\Totals;


use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Tax;

class TaxTotal
{
    /**
     * @var Tax
     */
    private $tax;
    /**
     * @var int
     */
    private $total;
    /**
     * @var TotalsCalculator
     */
    private $totals;
    /**
     * @var ShippingTotal
     */
    private $shippingTotal;

    public function __construct(Tax $tax, ShippingTotal $shippingTotal, TotalsCalculator $totals)
    {
        $this->tax    = $tax;
        $this->totals = $totals;
        $this->shippingTotal = $shippingTotal;

        $this->calculate();
    }

    protected function calculate()
    {
        $this->total = $this->totalProductTaxes() + $this->totalShippingTaxes();

        return $this->total;
    }

    public function total(): int
    {
        return $this->total;
    }

    protected function totalProductTaxes(): int
    {
        return $this->totals->getCart()->products->filter(function (CartProduct $product) {
            return $product->data->taxes->contains($this->tax->id);
        })->reduce(function ($total, CartProduct $product) {
            return $total += $product->totalForTax($this->tax);
        }, 0);
    }

    protected function totalShippingTaxes(): int
    {
        $cart = $this->totals->getCart();
        if ( ! $cart->shipping_method) {
            return 0;
        }

        if ( ! $cart->shipping_method->taxes->contains($this->tax->id)) {
            return 0;
        }

        $price = $this->shippingTotal->price();

        return $cart->shipping_method->taxes->reduce(function ($total, Tax $tax) use ($cart, $price) {
            return $total += $tax->percentageDecimal * $price;
        }, 0);
    }
}