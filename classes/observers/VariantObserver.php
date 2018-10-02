<?php

namespace OFFLINE\Mall\Classes\Observers;

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
        (new ProductObserver($this->index))->updated($variant->product);
    }

    public function updated(Variant $variant)
    {
        (new ProductObserver($this->index))->updated($variant->product);
    }

    public function deleted(Variant $variant)
    {
        (new ProductObserver($this->index))->updated($variant->product);
        $this->index->delete(VariantEntry::INDEX, $variant->id);
    }
}
