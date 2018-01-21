<?php

namespace OFFLINE\Mall\Classes\Totals;

use OFFLINE\Mall\Models\Tax;

class TaxTotal
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
}
