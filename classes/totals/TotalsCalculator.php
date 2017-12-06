<?php

namespace OFFLINE\Mall\Classes\Totals;


use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Discount;
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
    /**
     * @var int
     */
    protected $productTotal;
    /**
     * @var int
     */
    protected $productTaxes;

    public function __construct(Cart $cart)
    {
        $this->cart  = $cart->load(
            'products',
            'products.data.taxes',
            'shipping_method',
            'shipping_method.taxes',
            'shipping_method.rates',
            'discounts'
        );
        $this->taxes = new Collection();

        $this->calculate();
    }

    protected function calculate()
    {
        $this->weightTotal  = $this->calculateWeightTotal();
        $this->productTotal = $this->calculateProductTotal();
        $this->productTaxes = $this->calculateProductTaxes();

        $this->shippingTotal  = new ShippingTotal($this->cart->shipping_method, $this);
        $this->totalPreTaxes  = $this->productTotal + $this->shippingTotal->total();
        $this->totalTaxes     = $this->productTaxes + $this->shippingTotal->taxes();
        $this->totalPostTaxes = $this->totalPreTaxes + $this->totalTaxes;

        $this->taxes = $this->getTaxTotals();
    }

    protected function calculateProductTotal(): int
    {
        $total = $this->cart->products->reduce(function ($total, CartProduct $product) {
            return $total += $product->totalPreTaxes;
        }, 0);

        $total = $this->applyTotalDiscounts($total);

        return $total > 0 ? $total : 0;
    }

    protected function calculateProductTaxes(): int
    {
        return $this->cart->products->reduce(function ($total, CartProduct $product) {
            return $total += $product->totalTaxes;
        }, 0);
    }

    protected function getTaxTotals(): Collection
    {
        return $this->cart->products->flatMap(function (CartProduct $product) {
            return $product->data->taxes;
        })->unique()->map(function (Tax $tax) {
            return new TaxTotal($tax, $this->shippingTotal, $this);
        });
    }

    protected function calculateWeightTotal(): int
    {
        return $this->cart->products->reduce(function ($total, CartProduct $product) {
            return $total += $product->data->weight * $product->quantity;
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

    public function productTotal(): int
    {
        return $this->productTotal;
    }

    public function productTaxes(): int
    {
        return $this->productTaxes;
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

    /**
     * Apply the discounts that are applied to the cart's total.
     *
     * @param $total
     *
     * @return int
     */
    private function applyTotalDiscounts($total): int
    {
        $alternatePrice = $this->cart->discounts->where('type', 'alternate_price')->first();
        if ($alternatePrice) {
            return $alternatePrice->getOriginal('alternate_price');
        }

        $total = $this->cart->discounts->where('type', 'fixed_amount')->reduce(function ($total, Discount $discount) {
            return $total - $discount->amount;
        }, $total);

        $base = $total;

        return $this->cart->discounts->where('type', 'rate')->reduce(function ($total, Discount $discount) use ($base) {
            return $total - $base * ($discount->rate / 100);
        }, $total);
    }
}
