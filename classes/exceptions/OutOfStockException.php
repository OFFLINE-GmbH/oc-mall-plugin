<?php

namespace OFFLINE\Mall\Classes\Exceptions;

class OutOfStockException extends \RuntimeException
{
    public $product;

    public function __construct($product)
    {
        $this->product = $product;
        parent::__construct(
            sprintf('The product %s is currently not in stock.', $this->product->name),
            422
        );
    }
}
