<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Exceptions;

use RuntimeException;

class PriceBagException extends RuntimeException
{
    /**
     * Create a new exception.
     * @param Product|Variant $product
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
