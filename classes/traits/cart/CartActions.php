<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;

use DB;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

trait CartActions
{
    /**
     * Adds a product to the cart.
     *
     * @param Product $product
     * @param Variant $variant
     * @param int|null $quantity
     * @param Collection $values
     *
     * @return Cart
     */
    public function addProduct(
        Product $product,
        ?int $quantity = null,
        ?Variant $variant = null,
        ?Collection $values = null,
        ?array $serviceOptionIds = []
    ) {
        return DB::transaction(function () use ($product, $quantity, $variant, $values, $serviceOptionIds) {
            if (! $this->exists) {
                $this->save();
            }

            $quantity ??= $product->quantity_default ?? 1;

            $matchingProductInCart = $this->getMatchingProductInCart($product, $variant, $values);

            $isStackable = $product->stackable && count($serviceOptionIds) === 0 && $matchingProductInCart;

            if ($isStackable) {
                $newQuantity = $product->normalizeQuantity($matchingProductInCart->quantity + $quantity, $product);

                $this->validateStock($variant ?? $product, $quantity);
                $matchingProductInCart->update(['quantity' => $newQuantity]);
                
                $this->validateShippingMethod();

                return $this->load('products');
            }

            $quantity = $product->normalizeQuantity($quantity);
            $price    = $variant
                ? $variant->priceIncludingCustomFieldValues($values)
                : $product->priceIncludingCustomFieldValues($values);

            $this->validateStock($variant ?? $product, $quantity);

            $cartEntry             = new CartProduct();
            $cartEntry->cart_id    = $this->id;
            $cartEntry->product_id = $product->id;
            $cartEntry->variant_id = $variant ? $variant->id : null;
            $cartEntry->quantity   = $quantity;
            $cartEntry->weight     = $variant ? $variant->weight : $product->weight;
            // Skip any setter methods from the JsonPrice trait
            $cartEntry->attributes['price'] = $cartEntry->mapJsonPrice($price, 1);
            
            $this->products()->save($cartEntry);
            $this->load('products');

            if ($values) {
                $cartEntry->custom_field_values()->saveMany($values);
            }

            $this->validateShippingMethod();

            $cartEntry->service_options()->attach($serviceOptionIds);

            return $cartEntry;
        });
    }

    public function removeProduct(CartProduct $product)
    {
        $product->delete();
        $this->validateShippingMethod();

        return $this;
    }

    protected function validateStock($item, $quantity, $ignoreRecord = null)
    {
        $alreadyInCart = $this->getTotalQuantityInCart($item, $ignoreRecord);

        if ($item->allow_out_of_stock_purchases !== true && $item->stock < $quantity + $alreadyInCart) {
            throw new OutOfStockException($item);
        }
    }

    protected function getTotalQuantityInCart($item, $ignoreRecord): int
    {
        $query = CartProduct::where('cart_id', $this->id)
            ->when($ignoreRecord, function ($q) use ($ignoreRecord) {
                $q->where('id', '<>', $ignoreRecord);
            })
            ->when($item instanceof Product, function ($q) use ($item) {
                $q->where('product_id', $item->id);
            })
            ->when($item instanceof Variant, function ($q) use ($item) {
                $q->where('product_id', $item->product_id)
                    ->where('variant_id', $item->id);
            });

        return (int)$query->sum('quantity');
    }
}
