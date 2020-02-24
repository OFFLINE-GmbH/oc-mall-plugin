<?php

namespace OFFLINE\Mall\Classes\Totals;

use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\PaymentMethod;

class PaymentTotal implements \JsonSerializable
{
    use Rounding;
    /**
     * @var TotalsCalculator
     */
    private $totals;
    /**
     * @var PaymentMethod
     */
    private $method;
    /**
     * @var int
     */
    private $preTaxes;
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $taxes;
    /**
     * @var int
     */
    protected $price;

    public function __construct(?PaymentMethod $method, TotalsCalculator $totals)
    {
        $this->method = $method;
        $this->totals = $totals;

        $this->calculate();
    }

    protected function calculate()
    {
        $this->preTaxes = $this->calculatePreTax();
        $this->total    = $this->calculateTotal();
        $this->taxes    = $this->calculateTaxes();
    }

    protected function calculatePreTax(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $base = $this->totals->totalPrePayment();

        $percentage = $this->getPercentage();
        $price      = $this->getPrice();

        $charge = $this->getCharge($base, $price, $percentage);

        return $this->round($charge - $base);
    }

    protected function calculateTotal(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $base = $this->totals->totalPrePayment();

        $taxPercentage = $this->totals->paymentTaxes->sum('percentageDecimal') + 1;

        $percentage = $this->getPercentage();
        $price      = $this->getPrice();

        // Add total tax percentage.
        $percentage *= $taxPercentage;
        $price      *= $taxPercentage;

        $charge = $this->getCharge($base, $price, $percentage);

        return $this->round($charge - $base);
    }

    protected function calculateTaxes(): float
    {
        return $this->round($this->total - $this->preTaxes);
    }

    public function totalPreTaxes(): float
    {
        return $this->preTaxes;
    }

    public function totalPreTaxesOriginal(): float
    {
        return $this->preTaxes;
    }

    public function totalTaxes(): float
    {
        return $this->taxes;
    }

    public function totalPostTaxes(): float
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    protected function getPrice()
    {
        if ($this->price) {
            return $this->price;
        }

        $price = $this->method->price()->integer ?? 0;

        $this->price = $price;

        return $price;
    }

    /**
     * Add the fixed and percental amount to the base price.
     *
     * @param $base
     * @param $price
     * @param $percentage
     *
     * @return float|int
     */
    protected function getCharge($base, $price, $percentage)
    {
        return ($base + $price) / (1 - $percentage);
    }

    /**
     * @return int|mixed|string
     */
    protected function getPercentage()
    {
        return ($this->method->fee_percentage ?? 0) / 100;
    }

    public function jsonSerialize()
    {
        return [
            'method'   => $this->method,
            'preTaxes' => $this->preTaxes,
            'taxes'    => $this->taxes,
            'total'    => $this->total,
        ];
    }

    public function __toString()
    {
        return (string)json_encode($this->jsonSerialize());
    }
}