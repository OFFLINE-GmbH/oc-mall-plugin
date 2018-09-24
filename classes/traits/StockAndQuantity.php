<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Models\Variant;

trait StockAndQuantity
{
    public function reduceStock(int $quantity, bool $updateSalesCount = true)
    {
        $this->stock -= $quantity;
        if ($this->stock < 0 && $this->allow_out_of_stock_purchases !== true) {
            throw new OutOfStockException($this);
        }

        if ( $updateSalesCount) {
            $this->sales_count += $quantity;
            if ($this instanceof Variant) {
                $this->product->sales_count += $quantity;
                $this->product->save();
            }
        }

        return tap($this)->save();
    }

    /**
     * Enforce min and max quantity values for a product.
     *
     * @return int
     */
    public function normalizeQuantity($quantity): int
    {
        if ($this->quantity_min && $quantity < $this->quantity_min) {
            return $this->quantity_min;
        }
        if ($this->quantity_max && $quantity > $this->quantity_max) {
            return $this->quantity_max;
        }

        return $quantity;
    }
}
