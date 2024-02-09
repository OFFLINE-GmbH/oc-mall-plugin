<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use Illuminate\Support\Collection;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Classes\Traits\FilteredTaxes;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\Tax;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing 
 * system with the PriceBag class construct instead.
 */
class TotalsCalculator implements CallsAnyMethod
{
    use FilteredTaxes, Rounding;

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
     * [SHIPPING] Amount of taxes for shipping.
     * @deprecated
     * @var Collection
     */
    public $shippingTaxes;

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
     * [PAYMENT] Filtered Collection of applied payment taxes.
     * @deprecated
     * @var Collection
     */
    public $paymentTaxes;

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
     * Calculate totals.
     * @return void
     */
    protected function calculate()
    {
        $this->weightTotal = $this->bag->productsWeight();
        
        $this->productPreTaxes = $this->bag->productsExclusive()->integer() + $this->bag->servicesExclusive()->integer();
        $this->productTaxes = $this->bag->productsTax()->getMinorAmount()->toInt() + $this->bag->servicesTax()->getMinorAmount()->toInt();
        $this->productPostTaxes = $this->bag->productsInclusive()->integer() + $this->bag->servicesInclusive()->integer();

        $this->shippingTaxes = $this->getFilteredTaxes(optional($this->input->shipping_method)->taxes ?? new Collection());
        $this->shippingTotal = new ShippingTotal($this->input->shipping_method, $this);

        $this->totalPrePayment = $this->bag->productsInclusive()->integer() + $this->bag->servicesInclusive()->integer() + $this->bag->shippingInclusive()->integer();
        $this->paymentTaxes = $this->getFilteredTaxes(optional($this->input->payment_method)->taxes ?? new Collection());
        $this->paymentTotal = new PaymentTotal($this->input->payment_method, $this);

        $this->totalPreTaxes = $this->bag->totalExclusive()->integer();
        $this->totalDiscounts = $this->bag->totalDiscount()->getMinorAmount()->toInt();
        $this->totalPostTaxes = $this->bag->totalInclusive()->integer();
        if ($this->totalPostTaxes < 0) {
            $this->totalPostTaxes = 0;
        }

        $this->taxes = $this->getTaxTotals();
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
     * Return all applied taxes.
     * @return Collection
     */    
    protected function getTaxTotals(): Collection
    {
        $collect = new Collection;

        // Taxes
        $groups = [
            ...$this->bag->productsTaxes(true),
            ...$this->bag->servicesTaxes(true),
            ...$this->bag->shippingTaxes(true) 
        ];
        foreach ($groups AS $group) {
            foreach ($group AS $type => $taxes) {
                $taxes = $type == 'vat' ? [$taxes] : $taxes;
                $taxes = !is_array($taxes) ? [$taxes] : $taxes;
                if (empty($taxes)) {
                    continue;
                }

                foreach ($taxes AS $tax) {
                    $model = new TaxTotal(
                        $tax['base']->getMinorAmount()->toInt(), 
                        new Tax(['percentage' => $tax['factor']])
                    );
                    $collect->add($model);
                }
            }
        }

        //@todo handle payment
        //$paymentTaxes = new Collection();
        //$paymentTotal = $this->paymentTotal->totalPreTaxesOriginal();
        //if ($this->paymentTaxes) {
        //    $paymentTaxes = $this->paymentTaxes->map(function (Tax $tax) use ($paymentTotal) {
        //        return new TaxTotal($paymentTotal, $tax);
        //    });
        //}

        // Set Taxes
        $this->totalTaxes = $this->bag->totalTax()->getMinorAmount()->toInt();
        $this->detailedTaxes = $collect;
        return $this->consolidateTaxes($collect);
    }

    /**
     * Consolidate same taxes.
     * @return Collection
     */
    protected function consolidateTaxes(Collection $taxTotals)
    {
        return $taxTotals->groupBy(function (TaxTotal $taxTotal) {
            return $taxTotal->tax->percentage;
        })->map(function (Collection $grouped) {
            $tax = $grouped->first()->tax;
            $preTax = $grouped->sum(function (TaxTotal $tax) {
                return $tax->preTax();
            });

            $taxTotal = new TaxTotal($preTax, $tax);
            $taxTotal->setTotal($grouped->sum(function (TaxTotal $type) use ($tax) {
                return (new TaxTotal($type->preTax(), $tax))->total();
            }));
            return $taxTotal;
        })->values();
    }

    /**
     * Return weight of all products.
     * @return int|float
     */
    public function weightTotal(): int|float
    {
        return $this->weightTotal;
    }

    /**
     * Return 1xclusive price of all products and services.
     * @return int|float
     */
    public function productPreTaxes(): int|float
    {
        return $this->productPreTaxes;
    }

    /**
     * Return amount of taxes for all products and services.
     * @return int|float
     */
    public function productTaxes(): int|float
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
    public function totalPrePayment(): int|float
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
    public function totalPreTaxes(): int|float
    {
        return $this->totalPreTaxes;
    }

    /**
     * Return mount of taxes for the whole bag.
     * @return int|float
     */
    public function totalTaxes(): int|float
    {
        return $this->totalTaxes;
    }

    /**
     * Return amount of discounts for the whole bag.
     * @return int|float
     */
    public function totalDiscounts(): int|float
    {
        return $this->totalDiscounts;
    }

    /**
     * Return inclusive price of the whole bag.
     * @return int|float
     */
    public function totalPostTaxes(): int|float
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
}
