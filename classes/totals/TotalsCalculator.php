<?php

namespace OFFLINE\Mall\Classes\Totals;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
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
     * @var Collection<TaxTotal>
     */
    protected $detailedTaxes;
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
    protected $productPreTaxes;
    /**
     * @var int
     */
    protected $productTaxes;
    /**
     * @var int
     */
    protected $productPostTaxes;

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
        $this->weightTotal      = $this->calculateWeightTotal();
        $this->productPreTaxes  = $this->calculateProductPreTaxes();
        $this->productTaxes     = $this->calculateProductTaxes();
        $this->productPostTaxes = $this->productPreTaxes + $this->productTaxes;

        $this->shippingTotal = new ShippingTotal($this->cart->shipping_method, $this);
        $this->totalPreTaxes = $this->productPreTaxes + $this->shippingTotal->preTaxes();

        $this->taxes = $this->getTaxTotals();

        $this->totalPostTaxes = $this->productPostTaxes + $this->shippingTotal->total();
    }

    protected function calculateProductPreTaxes(): int
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
        $shippingTaxes = new Collection();
        $shippingTotal = $this->shippingTotal->preTaxes();
        if ($this->cart->shipping_method) {
            $shippingTaxes = optional($this->cart->shipping_method)->taxes->map(function (Tax $tax) use ($shippingTotal) {
                return new TaxTotal($shippingTotal, $tax);
            });
        }

        $productTaxes = $this->cart->products->flatMap(function (CartProduct $product) {
            return $product->data->taxes;
        })->unique()->map(function (Tax $tax) {
            return new TaxTotal($this->productPreTaxes, $tax);
        });

        $combined = $productTaxes->concat($shippingTaxes);

        $this->totalTaxes = $combined->reduce(function ($total, TaxTotal $tax) {
            return $total += $tax->total();
        }, 0);

        $this->detailedTaxes = $combined;

        return $this->consolidateTaxes($combined);
    }

    /**
     * This method consolidates the same taxes on shipping
     * and products down to one combined TaxTotal.
     */
    protected function consolidateTaxes(Collection $taxTotals)
    {
        return $taxTotals->groupBy(function (TaxTotal $taxTotal) {
            return $taxTotal->tax->id;
        })->map(function (Collection $grouped) {
            $tax    = $grouped->first()->tax;
            $preTax = $grouped->reduce(function ($total, TaxTotal $tax) {
                return $total += $tax->preTax();
            }, 0);

            return new TaxTotal($preTax, $tax);
        })->values();
    }

    protected function calculateWeightTotal(): int
    {
        return $this->cart->products->reduce(function ($total, CartProduct $product) {
            return $total += $product->data->weight * $product->quantity;
        }, 0);
    }

    public function shippingTotal(): ShippingTotal
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

    public function productPreTaxes(): int
    {
        return $this->productPreTaxes;
    }

    public function productTaxes(): int
    {
        return $this->productTaxes;
    }

    public function productPostTaxes(): int
    {
        return $this->productPostTaxes;
    }

    public function totalPostTaxes(): int
    {
        return $this->totalPostTaxes;
    }

    public function taxes(bool $detailed = false): Collection
    {
        return $detailed ? $this->detailedTaxes : $this->taxes;
    }

    public function detailedTaxes(): Collection
    {
        return $this->taxes(true);
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * Process the discounts that are applied to the cart's total.
     *
     * @param $total
     *
     * @return int
     */
    private function applyTotalDiscounts($total): int
    {
        $discounts = $this->cart->discounts->reject(function ($item) {
            return $item->type === 'shipping';
        });

        $applier = new DiscountApplier($this->cart, $total);
        $applier->applyMany($discounts);

        return $applier->reducedTotal();
    }
}
