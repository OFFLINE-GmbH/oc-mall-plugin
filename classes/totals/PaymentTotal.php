<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use JsonSerializable;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\PaymentMethod;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing
 * system with the PriceBag class construct instead.
 */
class PaymentTotal implements JsonSerializable, CallsAnyMethod
{
    use Rounding;

    /**
     * TotalsCalculator instance.
     * @var TotalsCalculator
     */
    private $totals;

    /**
     * PaymentMethod instance.
     * @var PaymentMethod
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
     * Inclusive price.
     * @var float|int
     */
    private $total;

    /**
     * Create a new PaymentTotal instance.
     * @param PaymentMethod|null $method
     * @param TotalsCalculator $totals
     */
    public function __construct(?PaymentMethod $method, TotalsCalculator $totals)
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
            'method'   => $this->method,
            'preTaxes' => $this->preTaxes,
            'taxes'    => $this->taxes,
            'total'    => $this->total,
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
     * @return float|int
     */
    public function totalPreTaxesOriginal()
    {
        return $this->preTaxes;
    }

    /**
     * Receive amount of taxes.
     * @return float|int
     */
    public function totalTaxes()
    {
        return $this->taxes;
    }

    /**
     * Receive inclusive price value.
     * @return float|int
     */
    public function totalPostTaxes()
    {
        return $this->total;
    }

    /**
     * Calculate Shipping costs.
     * @return void
     */
    protected function calculate()
    {
        $bag = $this->totals->getBag();
        $this->preTaxes = $bag->paymentExclusive()->getMinorAmount()->toInt();
        $this->total = $bag->paymentFee()->getMinorAmount()->toInt();
        $this->taxes = $bag->paymentTax()->getMinorAmount()->toInt();
    }
}
