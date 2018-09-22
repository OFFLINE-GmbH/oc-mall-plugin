<?php

namespace OFFLINE\Mall\Classes\Observers;


use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Models\Product;

class ProductObserver
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function created(Product $product)
    {
        $this->index->insert(ProductEntry::INDEX, new ProductEntry($product));
        foreach ($product->variants as $variant) {
            $this->index->insert(VariantEntry::INDEX, new VariantEntry($variant));
        }
    }

    public function updated(Product $product)
    {
        $this->index->update(ProductEntry::INDEX, $product->id, new ProductEntry($product));
        foreach ($product->variants as $variant) {
            $this->index->update(VariantEntry::INDEX, $variant->id, new VariantEntry($variant));
        }
    }

    public function deleted(Product $product)
    {
        $this->index->delete(ProductEntry::INDEX, $product->id);
        foreach ($product->variants as $variant) {
            $this->index->delete(VariantEntry::INDEX, $variant->id);
        }
    }
}