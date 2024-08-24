<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Classes\Traits\FilteredTaxes;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Tax;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing
 * system with the PriceBag class construct instead.
 */
class TotalsCalculator implements CallsAnyMethod
{
    use FilteredTaxes;
    use Rounding;

    /**
     * [SHIPPING] Amount of taxes for shipping.
     * @deprecated
     * @var Collection
     */
    public $shippingTaxes;

    /**
     * [PAYMENT] Filtered Collection of applied payment taxes.
     * @deprecated
     * @var Collection
     */
    public $paymentTaxes;

    /**
     * TotalsCalculatorInput data collection.
     * @deprecated
     * @var TotalsCalculatorInput
     */
    protected $input;

    /**
     * Primary PriceBag instance.
     * @var PriceBag
     */
    protected PriceBag $bag;

    /**
     * [PRODUCTS] Weight of all products.
     * @var int|float
     */
    protected $weightTotal;

    /**
     * [PRODUCTS] Exclusive price of all products and services.
     * @var int|float
     */
    protected $productPreTaxes;

    /**
     * [PRODUCTS] Amount of taxes for all products and services.
     * @var int|float
     */
    protected $productTaxes;

    /**
     * [PRODUCTS] Inclusive price of all products and services.
     * @var int|float
     */
    protected $productPostTaxes;

    /**
     * [SHIPPING] ShippingTotal instance.
     * @deprecated
     * @var ShippingTotal
     */
    protected $shippingTotal;

    /**
     * [PAYMENT] Inclusive price of the whole bag without payment fees / discounts.
     * @var int|float
     */
    protected $totalPrePayment;

    /**
     * [PAYMENT] PaymentTotal instance.
     * @deprecated
     * @var PaymentTotal
     */
    protected $paymentTotal;

    /**
     * [TOTALS] Exclusive price of the whole bag.
     * @var int|float
     */
    protected $totalPreTaxes;

    /**
     * [TOTALS] Amount of taxes for the whole bag.
     * @var int|float
     */
    protected $totalTaxes;

    /**
     * [TOTALS] Amount of discounts for the whole bag.
     * @var int|float
     */
    protected $totalDiscounts;

    /**
     * [TOTALS] Inclusive price of the whole bag.
     * @var int|float
     */
    protected $totalPostTaxes;

    /**
     * Collection of all applied taxes.
     * @var Collection<TaxTotal>
     */
    protected $taxes;
    
    /**
     * Detailed Collection of all applied taxes.
     * @var Collection<TaxTotal>
     */
    protected $detailedTaxes;

    /**
     * Applied discount model collection.
     * @var Collection
     */
    protected $appliedDiscounts;

    /**
     * Create a new totalsCalculator instance.
     * @param TotalsCalculatorInput $input
     */
    public function __construct(TotalsCalculatorInput $input)
    {
        $this->input = $input;
        $this->bag = PriceBag::fromTotalsCalculatorInput($input);

        // @todo
        $this->bag->applyDiscounts();
        $this->calculate();
    }

    /**
     * Return used PriceBag instance.
     * @return PriceBag
     */
    public function getBag()
    {
        return clone $this->bag;
    }

    /**
     * Return applied discounts
     * @return Collection
     */
    public function appliedDiscounts(): Collection
    {
        return $this->appliedDiscounts;
    }

    /**
     * Return TotalsCalculatorInput property.
     * @return TotalsCalculatorInput
     */
    public function getInput(): TotalsCalculatorInput
    {
        return $this->input;
    }

    /**
     * Return weight of all products.
     * @return int|float
     */
    public function weightTotal()
    {
        return $this->weightTotal;
    }

    /**
     * Return 1xclusive price of all products and services.
     * @return int|float
     */
    public function productPreTaxes()
    {
        return $this->productPreTaxes;
    }

    /**
     * Return amount of taxes for all products and services.
     * @return int|float
     */
    public function productTaxes()
    {
        return $this->productTaxes;
    }

    /**
     * Return inclusive price of all products and services.
     * @return int|float
     */
    public function productPostTaxes(): float
    {
        return $this->productPostTaxes;
    }

    /**
     * Return obsolete ShippingTotal instance.
     * @deprecated
     * @return ShippingTotal
     */
    public function shippingTotal(): ShippingTotal
    {
        return $this->shippingTotal;
    }

    /**
     * Return inclusive price of the whole bag without payment fees / discounts.
     * @return int|float
     */
    public function totalPrePayment()
    {
        return $this->totalPrePayment;
    }

    /**
     * Return obsolete PaymentTotal instance.
     * @deprecated
     * @return PaymentTotal
     */
    public function paymentTotal(): PaymentTotal
    {
        return $this->paymentTotal;
    }

    /**
     * Return exclusive price of the whole bag.
     * @return int|float
     */
    public function totalPreTaxes()
    {
        return $this->totalPreTaxes;
    }

    /**
     * Return mount of taxes for the whole bag.
     * @return int|float
     */
    public function totalTaxes()
    {
        return $this->totalTaxes;
    }

    /**
     * Return amount of discounts for the whole bag.
     * @return int|float
     */
    public function totalDiscounts()
    {
        return $this->totalDiscounts;
    }

    /**
     * Return inclusive price of the whole bag.
     * @return int|float
     */
    public function totalPostTaxes()
    {
        return $this->totalPostTaxes;
    }

    /**
     * Return Collection of all taxes.
     * @param boolean $detailed
     * @return Collection
     */
    public function taxes(bool $detailed = false): Collection
    {
        return $detailed ? $this->detailedTaxes : $this->taxes;
    }

    /**
     * Return Collection of detailed taxes.
     * @return Collection
     */
    public function detailedTaxes(): Collection
    {
        return $this->taxes(true);
    }

    /**
     * Calculate totals.
     * @return void
     */
    protected function calculate()
    {
        $this->weightTotal = $this->bag->productsWeight();
        
        $this->productPreTaxes = $this->bag->productsExclusive()->toInt() + $this->bag->servicesExclusive()->toInt();
        $this->productTaxes = $this->bag->productsTax()->getMinorAmount()->toInt() + $this->bag->servicesTax()->getMinorAmount()->toInt();
        $this->productPostTaxes = $this->bag->productsInclusive()->toInt() + $this->bag->servicesInclusive()->toInt();

        $this->shippingTaxes = $this->getFilteredTaxes(optional($this->input->shipping_method)->taxes ?? new Collection());
        $this->shippingTotal = new ShippingTotal($this->input->shipping_method, $this);

        $this->totalPrePayment = $this->bag->productsInclusive()->toInt() + $this->bag->servicesInclusive()->toInt() + $this->bag->shippingInclusive()->toInt();
        $this->paymentTaxes = $this->getFilteredTaxes(optional($this->input->payment_method)->taxes ?? new Collection());
        $this->paymentTotal = new PaymentTotal($this->input->payment_method, $this);

        $this->totalPreTaxes = $this->bag->totalExclusive()->toInt();
        $this->totalDiscounts = $this->bag->totalDiscount()->getMinorAmount()->toInt();
        $this->totalPostTaxes = $this->bag->totalInclusive()->toInt();

        if ($this->totalPostTaxes < 0) {
            $this->totalPostTaxes = 0;
        }

        $this->taxes = $this->getTaxTotals();
        $this->applyTotalDiscounts($this->productPostTaxes);
    }
    
    /**
     * Process the discounts that are applied to the cart's total.
     * @param mixed $total
     * @return flaot
     */
    protected function applyTotalDiscounts($total): ?float
    {
        $nonCodeTriggers = Discount::whereIn('trigger', ['total', 'product', 'customer_group', 'shipping_method', 'payment_method'])
            ->with('shipping_methods')
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', Carbon::now());
            })->where(function ($q) {
                $q->whereNull('expires')
                    ->orWhere('expires', '>', Carbon::now());
            })->get();

        $discounts = $this->input->discounts->merge($nonCodeTriggers)->reject(fn ($item) => $item->type === 'shipping');

        $applier = new DiscountApplier($this->input, $total);
        $this->appliedDiscounts = $applier->applyMany($discounts);

        return $applier->reducedTotal();
    }

    /**
     * Return all applied taxes.
     * @return Collection
     */
    protected function getTaxTotals(): Collection
    {
        $collect = new Collection();

        // Taxes
        $groups = [
            ...$this->bag->productsTaxes(true),
            ...$this->bag->servicesTaxes(true),
            ...$this->bag->shippingTaxes(true),
        ];

        foreach ($groups as $group) {
            foreach ($group as $type => $taxes) {
                $taxes = $type == 'vat' ? [$taxes] : $taxes;
                $taxes = !is_array($taxes) ? [$taxes] : $taxes;

                if (empty($taxes)) {
                    continue;
                }

                foreach ($taxes as $tax) {
                    $model = new TaxTotal(
                        $tax['base']->getMinorAmount()->toInt(),
                        new Tax(['percentage' => $tax['factor']])
                    );
                    $collect->add($model);
                }
            }
        }
        $consolidatedTaxes = $this->consolidateTaxes(clone $collect);

        // Set payment taxes
        $paymentTax = $this->bag->paymentTax();

        if ($paymentTax->getMinorAmount()->toInt() > 0) {
            $model = new TaxTotal(
                $paymentTax->getMinorAmount()->toInt(),
                new Tax(['percentage' => 0])
            );
            $model->setTotal($paymentTax->getMinorAmount()->toInt());
            $collect->add($model);
            $consolidatedTaxes->add(clone $model);
        }

        // Set Taxes
        $this->totalTaxes = $this->bag->totalTax()->getMinorAmount()->toInt();
        $this->detailedTaxes = $collect;

        return $consolidatedTaxes;
    }

    /**
     * Consolidate same taxes.
     * @return Collection
     */
    protected function consolidateTaxes(Collection $taxTotals)
    {
        return $taxTotals->groupBy(fn (TaxTotal $taxTotal) => $taxTotal->tax->percentage)->map(function (Collection $grouped) {
            $tax = $grouped->first()->tax;
            $preTax = $grouped->sum(fn (TaxTotal $tax) => $tax->preTax());

            $taxTotal = new TaxTotal($preTax, $tax);
            $taxTotal->setTotal($grouped->sum(fn (TaxTotal $type) => (new TaxTotal($type->preTax(), $tax))->total()));

            return $taxTotal;
        })->values();
    }
}
