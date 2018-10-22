<?php

namespace OFFLINE\Mall\Classes\Exceptions;

class OutOfStockException extends \RuntimeException
{
    public $product;

    public function __construct($product)
    {
        $this->product = $product;
        parent::__construct('This product is currently not in stock.', 422);
    }
}
