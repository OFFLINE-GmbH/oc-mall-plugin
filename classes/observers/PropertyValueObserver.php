<?php

namespace OFFLINE\Mall\Classes\Observers;


use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Models\PropertyValue;

class PropertyValueObserver
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function created(PropertyValue $value)
    {
        if ($value->product) {
            $this->index->update(ProductEntry::INDEX, $value->product->id, new ProductEntry($value->product));
        }
        if ($value->variant) {
            $this->index->update(VariantEntry::INDEX, $value->variant->id, new VariantEntry($value->variant));
        }
    }

    public function updated(PropertyValue $value)
    {
        if ($value->product) {
            $this->index->update(ProductEntry::INDEX, $value->product->id, new ProductEntry($value->product));
        }
        if ($value->variant) {
            $this->index->update(VariantEntry::INDEX, $value->variant->id, new VariantEntry($value->variant));
        }
    }

    public function deleted(PropertyValue $value)
    {
        if ($value->product) {
            $this->index->update(ProductEntry::INDEX, $value->product->id, new ProductEntry($value->product));
        }
        if ($value->variant) {
            $this->index->update(VariantEntry::INDEX, $value->variant->id, new VariantEntry($value->variant));
        }
    }
}
