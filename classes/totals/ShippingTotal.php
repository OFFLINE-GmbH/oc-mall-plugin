<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use JsonSerializable;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\ShippingMethod;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing
 * system with the PriceBag class construct instead.
 */
class ShippingTotal implements JsonSerializable, CallsAnyMethod
{
    use Rounding;

    /**
     * TotalsCalculator instance.
     * @var TotalsCalculator
     */
    private $totals;

    /**
     * ShippingMethod instance.
     * @var ShippingMethod
     */
    private $method;

    /**
     * Exclusive price.
     * @var float|int
     */
    private $preTaxes;

    /**
     * Amount of Taxes.
     * @var float|int
     */
    private $taxes;

    /**
     * Amount of discount.
     * @var float|int
     */
    private $appliedDiscount;

    /**
     * Inclusive price.
     * @var float|int
     */
    private $total;

    /**
     * Create a new ShippingTotal instance.
     * @param ShippingMethod|null $method
     * @param TotalsCalculator $totals
     */
    public function __construct(?ShippingMethod $method, TotalsCalculator $totals)
    {
        $this->method = $method;
        $this->totals = $totals;

        $this->calculate();
    }

    /**
     * String-Representation of this class instance.
     * @return string
     */
    public function __toString()
    {
        return (string)json_encode($this->jsonSerialize());
    }

    /**
     * JSON serialize class.
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'method'            => $this->method(),
            'preTaxes'          => $this->preTaxes,
            'taxes'             => $this->taxes,
            'total'             => $this->total,
            'appliedDiscount'   => $this->appliedDiscount,
        ];
    }

    /**
     * Receive exclusive price value.
     * @return float|int
     */
    public function totalPreTaxes()
    {
        return $this->preTaxes;
    }

    /**
     * Receive exclusive price value.
     * @return float
     */
    public function totalPreTaxesOriginal()
    {
        return $this->preTaxes;
    }

    /**
     * Receive amount of taxes.
     * @return float
     */
    public function totalTaxes()
    {
        return $this->taxes;
    }

    /**
     * Receive amount of applied discount.
     * @return float
     */
    public function appliedDiscount()
    {
        return $this->appliedDiscount;
    }

    /**
     * Receive inclusive price value.
     * @return float
     */
    public function totalPostTaxes(): float
    {
        return $this->total;
    }

    /**
     * Get the effective ShippingMethod including changes made by any applied discounts.
     * @return ?ShippingMethod
     */
    public function method(): ?ShippingMethod
    {
        if ($this->totals->getInput()->products->every('data.is_virtual')) {
            return ShippingMethod::noShippingRequired();
        }

        if (!$this->appliedDiscount) {
            return $this->method;
        }

        $method = $this->method->replicate(['id', 'name']);
        $discount = $this->totals->getBag()->get('shipping')[0]->get('discountModel');

        if ($discount) {
            $method->name = $discount->shipping_description;
            $method->setRelation('prices', $discount->shipping_prices);
        }

        return $method;
    }

    /**
     * Calculate Shipping costs.
     * @return void
     */
    protected function calculate()
    {
        $method = $this->totals->getBag()->get('shipping');

        $this->preTaxes = $this->totals->getBag()->shippingExclusive()->toInt();
        $this->taxes = $this->totals->getBag()->shippingTax()->getMinorAmount()->toInt();
        $this->appliedDiscount = empty($method) ? null : ($method[0]->get('discountModel') ?? null);
        $this->total = $this->totals->getBag()->shippingInclusive()->toInt();
    }
}
