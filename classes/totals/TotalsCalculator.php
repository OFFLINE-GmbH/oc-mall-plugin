<?php

namespace OFFLINE\Mall\Classes\Totals;


use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Tax;

class TotalsCalculator
{
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Collection<TaxTotal>
     */
    protected $taxes;
    /**
     * @var ShippingTotal
     */
    protected $shippingTotal;
    /**
     * @var int
     */
    protected $weightTotal;
    /**
     * @var int
     */
    protected $totalPreTaxes;
    /**
     * @var int
     */
    protected $totalPostTaxes;
    /**
     * @var int
     */
    protected $totalTaxes;

    public function __construct(Cart $cart)
    {
        $this->cart  = $cart->load(
            'products',
            'products.taxes',
            'shipping_method',
            'shipping_method.taxes',
            'shipping_method.rates'
        );
        $this->taxes = new Collection();

        $this->calculate();
    }

    protected function calculate()
    {
        $this->weightTotal = $this->cart->products->reduce(function ($total, Product $product) {
            return $total += $product->pivot->weight;
        }, 0);

        $this->shippingTotal = new ShippingTotal($this->cart->shipping_method, $this);

        $this->totalPreTaxes  = $this->productTotal() + $this->shippingTotal->total();
        $this->totalTaxes     = $this->productTaxes() + $this->shippingTotal->taxes();
        $this->totalPostTaxes = $this->totalPreTaxes + $this->totalTaxes;

        $this->taxes = $this->getTaxTotals();
    }

    protected function productTotal(): int
    {
        return $this->cart->products->reduce(function ($total, Product $product) {
            return $total += $product->pivot->totalPreTaxes;
        }, 0);
    }

    protected function productTaxes(): int
    {
        return $this->cart->products->reduce(function ($total, Product $product) {
            return $total += $product->pivot->totalTaxes;
        }, 0);
    }

    protected function shippingTotal(): ShippingTotal
    {
        return $this->shippingTotal;
    }

    public function weightTotal(): int
    {
        return $this->weightTotal;
    }

    public function totalPreTaxes(): int
    {
        return $this->totalPreTaxes;
    }

    public function totalTaxes(): int
    {
        return $this->totalTaxes;
    }

    public function totalPostTaxes(): int
    {
        return $this->totalPostTaxes;
    }

    public function taxes(): Collection
    {
        return $this->taxes;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    protected function getTaxTotals(): Collection
    {
        return $this->cart->products->flatMap(function (Product $product) {
            return $product->taxes;
        })->unique()->map(function (Tax $tax) {
            return new TaxTotal($tax, $this->shippingTotal, $this);
        });
    }
}
