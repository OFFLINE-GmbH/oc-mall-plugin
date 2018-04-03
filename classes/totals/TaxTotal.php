<?php

namespace OFFLINE\Mall\Classes\Totals;

use JsonSerializable;
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

    public function __construct(float $preTax, Tax $tax)
    {
        $this->preTax = $preTax;
        $this->tax    = $tax;

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
            'amount_formatted' => format_money(round($this->preTax)),
            'total_formatted'  => format_money(round($this->total)),
        ];
    }
}
