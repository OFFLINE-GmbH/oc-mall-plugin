<?php

namespace OFFLINE\Mall\Classes\Observers;

use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Models\Variant;

class VariantObserver
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function created(Variant $variant)
    {
        $this->index->insert(VariantEntry::INDEX, new VariantEntry($variant));
        (new ProductObserver($this->index))->updated(($variant->product));
    }

    public function updated(Variant $variant)
    {
        $this->index->update(VariantEntry::INDEX, $variant->id, new VariantEntry($variant));
        (new ProductObserver($this->index))->updated(($variant->product));
    }

    public function deleted(Variant $variant)
    {
        $this->index->update(ProductEntry::INDEX, $variant->product->id, new ProductEntry($variant->product));
        (new ProductObserver($this->index))->deleted(($variant->product));
    }
}
