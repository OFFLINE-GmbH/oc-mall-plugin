<?php

namespace OFFLINE\Mall\Classes\Totals;

use JsonSerializable;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Tax;

class TaxTotal implements JsonSerializable
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

    public function __construct(float $preTax, Tax $tax)
    {
        $this->preTax = $preTax;
        $this->tax = $tax;
        $this->money = app(Money::class);

        $this->calculate();
    }

    public function setTotal(float $total) {
        $this->total = $total;
    }

    protected function calculate()
    {
        $this->total = $this->preTax * $this->tax->percentageDecimal;

        return $this->total;
    }

    public function total(): float
    {
        return $this->round($this->total);
    }

    public function preTax(): float
    {
        return $this->preTax;
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

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
}
