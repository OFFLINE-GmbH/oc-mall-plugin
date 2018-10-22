<?php

namespace OFFLINE\Mall\Classes\Totals;

use JsonSerializable;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Tax;

class TaxTotal implements JsonSerializable
{
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
        $this->tax    = $tax;
        $this->money  = app(Money::class);

        $this->calculate();
    }

    protected function calculate()
    {
        $this->total = $this->preTax * $this->tax->percentageDecimal;

        return $this->total;
    }

    public function total(): float
    {
        return $this->total;
    }

    public function preTax(): float
    {
        return $this->preTax;
    }

    public function __toArray(): array
    {
    }

    public function jsonSerialize()
    {
        return [
            'tax'              => $this->tax,
            'amount'           => round($this->preTax),
            'total'            => round($this->total),
            'amount_formatted' => $this->money->format(round($this->preTax)),
            'total_formatted'  => $this->money->format(round($this->total)),
        ];
    }
}
