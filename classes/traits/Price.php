<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait Price
{
    public function getPriceColumns(): array
    {
        return ['price'];
    }

    public function setAttribute($key, $value)
    {
        if (\in_array($value, [0, null, ''], true)) {
            return $this->attributes[$key] = null;
        }

        if ( ! $this->isPriceColumn($key)) {
            return parent::setAttribute($key, $value);
        }

        $this->attributes[$key] = (float)$value * 100;
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        // If the model already implements an accessor we don't mess with the attribute.
        if (method_exists($this, sprintf('get%sAttribute', studly_case($key)))) {
            return $value;
        }

        if ( ! $this->isPriceColumn($key)) {
            return $value;
        }

        if ($value === null) {
            return $value;
        }

        return $this->roundPrice($value);
    }

    public function getAttribute($attr)
    {
        if (ends_with($attr, '_formatted')) {
            $attr = str_replace('_formatted', '', $attr);
            if ( ! $this->isPriceColumn($attr)) {
                return parent::getAttribute($attr);
            }

            return $this->formatPrice(parent::getAttribute($attr));
        }

        return parent::getAttribute($attr);
    }

    public function formatPrice($price)
    {
        $product = null;
        if ($this instanceof Product) {
            $product = $this;
        }
        if ($this instanceof Variant) {
            $product = $this->product;
        }

        return format_money($price, $product);
    }

    protected function isPriceColumn($key): bool
    {
        return in_array($key, $this->getPriceColumns());
    }

    /**
     * @param $value
     *
     * @return float
     */
    protected function roundPrice($value): float
    {
        return round((int)$value / 100, 2);
    }
}
