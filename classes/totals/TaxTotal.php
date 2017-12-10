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
     * @var int
     */
    private $preTax;
    /**
     * @var int
     */
    private $total;

    public function __construct(int $preTax, Tax $tax)
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

    public function total(): int
    {
        return $this->total;
    }

    public function preTax(): int
    {
        return $this->preTax;
    }
}
