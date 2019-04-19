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
        $productEntry = new ProductEntry($product);

        if ($product->inventory_management_method === 'single' || $product->variants->count() > 0) {
            $this->index->insert(ProductEntry::INDEX, $productEntry);
        }

        // If a product has no variants we still want it in the variant index
        // so we can easily search all products/variants at once.
        if ($product->inventory_management_method === 'single') {
            $this->index->insert(VariantEntry::INDEX, $this->ghostVariant($product, $productEntry));
        } else {
            $product->variants->load(['prices.currency', 'property_values.property', 'product.brand']);
            foreach ($product->variants as $variant) {
                $this->index->insert(VariantEntry::INDEX, new VariantEntry($variant));
            }
        }
    }

    public function updated(Product $product)
    {
        // If a re-index is forced skip this run, it will be triggered manually later on
        if ($product->forceReindex) {
            return;
        }

        $product->load('property_values', 'variants.product_property_values', 'variants.property_values');

        $productEntry = new ProductEntry($product);
        if ($product->inventory_management_method === 'single' || $product->variants->count() > 0) {
            $this->index->update(ProductEntry::INDEX, $product->id, $productEntry);
        } else {
            $this->index->delete(ProductEntry::INDEX, $product->id, $productEntry);
        }

        if ($product->inventory_management_method === 'single') {
            $this->handleInventoryManagementMethodChange($product);
            $this->index->update(
                VariantEntry::INDEX,
                $this->ghostId($product),
                $this->ghostVariant($product, $productEntry)
            );
        } else {
            $this->index->delete(VariantEntry::INDEX, $this->ghostId($product));

            foreach ($product->variants as $variant) {
                $this->index->update(VariantEntry::INDEX, $variant->id, new VariantEntry($variant));
            }
        }
    }

    public function deleted(Product $product)
    {
        $this->index->delete(ProductEntry::INDEX, $product->id);
        if ($product->inventory_management_method === 'single') {
            $this->index->delete(VariantEntry::INDEX, $this->ghostId($product));
        } else {
            $this->removeVariantsFromIndex($product);
        }
    }

    /**
     * Create a ghost variant entry that is actually a product.
     * This enables us to query variants and products without
     * any variants from the same index.
     *
     * @param Product $product
     * @param         $productEntry
     *
     * @return mixed
     */
    protected function ghostVariant(Product $product, $productEntry)
    {
        return $productEntry->withData([
            'id'            => $this->ghostId($product),
            'product_id'    => $product->id,
            'index'         => VariantEntry::INDEX,
            'ghost_variant' => true,
        ]);
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    protected function ghostId(Product $product): string
    {
        return 'product-' . $product->id;
    }

    /**
     * @param Product $product
     */
    protected function handleInventoryManagementMethodChange(Product $product)
    {
        $methodWas = $product->getOriginal('inventory_management_method');
        $methodIs  = $product->inventory_management_method;
        if ($methodWas === 'variant' && $methodIs === 'single') {
            $this->removeVariantsFromIndex($product);
        }
    }

    /**
     * @param Product $product
     */
    protected function removeVariantsFromIndex(Product $product)
    {
        foreach ($product->variants as $variant) {
            $this->index->delete(VariantEntry::INDEX, $variant->id);
        }
    }

    /**
     * Default product relations.
     */
    protected function with(): array
    {
        return [
            'categories',
            'brand',
            'variants.prices.currency',
            'variants.property_values.property',
            'prices.currency',
            'property_values.property',
        ];
    }
}
