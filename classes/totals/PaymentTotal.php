<?php declare(strict_types=1);

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
     * Calculate Shipping costs.
     * @return void
     */
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
}