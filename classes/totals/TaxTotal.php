<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Totals;

use Brick\Money\Money as BrickMoney;
use JsonSerializable;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Tax;
use Whitecube\Price\Price;

/**
 * @deprecated Since version 3.2.0, will be removed in 3.4.0 or later. Please use the new Pricing
 * system with the PriceBag class construct instead.
 */
class TaxTotal implements JsonSerializable, CallsAnyMethod
{
    use Rounding;

    /**
     * @var Tax
     */
    public $tax;

    /**
     * @var float
     */
    private $preTax;

    /**
     * @var float
     */
    private $total;

    /**
     * @var Money
     */
    private $money;

    /**
     * Create a new TaxTotal instance.
     * @param float $preTax
     * @param Tax $tax
     */
    public function __construct(float $preTax, Tax $tax)
    {
        $this->preTax = $preTax;
        $this->tax = $tax;
        $this->money = app(Money::class);

        $this->calculate();
    }

    /**
     * String-Representation of this class instance.
     * @return string
     */
    public function __toString()
    {
        return (string) json_encode($this->jsonSerialize());
    }

    /**
     * Array-Representation of this class instance.
     * @return string
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * JSON serialize class.
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'tax'              => $this->tax->toArray(),
            'amount'           => round($this->preTax),
            'total'            => round($this->total),
            'amount_formatted' => $this->money->format(round($this->preTax)),
            'total_formatted'  => $this->money->format(round($this->total)),
        ];
    }

    /**
     * Set total amount.
     * @param float $total
     * @return void
     */
    public function setTotal(float $total)
    {
        $this->total = $total;
    }

    /**
     * Return total amount.
     * @return int|float
     */
    public function total()
    {
        return $this->round($this->total);
    }

    /**
     * Return total amount before taxes applied.
     * @return int|float
     */
    public function preTax()
    {
        return $this->preTax;
    }

    /**
     * Calculate total amount
     * @return void
     */
    protected function calculate(): void
    {
        $currency = $currency ?? Currency::activeCurrency();

        $total = new Price(BrickMoney::ofMinor($this->preTax, $currency->code));
        $total->setVat($this->tax->percentage);

        /** @var BrickMoney */
        $money = $total->vat()->money();
        $this->total = $money->getMinorAmount()->toInt();
    }
}
