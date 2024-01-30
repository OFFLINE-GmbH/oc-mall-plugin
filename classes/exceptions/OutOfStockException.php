<?php

namespace OFFLINE\Mall\Classes\Exceptions;

use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

class OutOfStockException extends \RuntimeException
{
    public $product;

    public function __construct(Product|Variant $product)
    {
        $this->product = $product;
        parent::__construct(
            sprintf('The product %s is currently not in stock.', $this->product->name),
            422
        );
    }
}
