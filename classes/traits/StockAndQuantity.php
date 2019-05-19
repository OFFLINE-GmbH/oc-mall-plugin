<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Models\Variant;

trait StockAndQuantity
{
    public function reduceStock(int $quantity, bool $updateSalesCount = true)
    {
        $this->decrement('stock', $quantity);

        $fresh = $this->fresh();
        if ($fresh->stock < 0 && $this->allow_out_of_stock_purchases !== true) {
            throw new OutOfStockException($fresh);
        }

        if ($updateSalesCount) {
            $this->increment('sales_count', $quantity);
            if ($this instanceof Variant) {
                $this->product->increment('sales_count', $quantity);
            }
        }

        return $this;
    }

    /**
     * Enforce min and max quantity values for a product.
     *
     * @return int
     */
    public function normalizeQuantity($quantity): int
    {
        if ($quantity < 1) {
            $quantity = 1;
        }
        if ($this->quantity_min && $quantity < $this->quantity_min) {
            return $this->quantity_min;
        }
        if ($this->quantity_max && $quantity > $this->quantity_max) {
            return $this->quantity_max;
        }

        return $quantity;
    }

    /**
     * Check if this model is in stock.
     * @return bool
     */
    public function isInStock()
    {
        return $this->allow_out_of_stock_purchases === true || $this->stock > 0;
    }
}
