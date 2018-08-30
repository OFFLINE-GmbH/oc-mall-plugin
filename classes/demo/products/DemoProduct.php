<?php

namespace OFFLINE\Mall\Classes\Demo\Products;


use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

abstract class DemoProduct
{
    public $product;
    public $imageSets;

    abstract protected function attributes(): array;

    abstract protected function properties(): array;

    abstract protected function variants(): array;

    abstract protected function customFields(): array;

    abstract protected function taxes(): array;

    abstract protected function images(): array;

    abstract protected function prices(): array;

    protected function additionalPrices(): array
    {
        return [];
    }

    public function create()
    {
        $this->product = Product::create($this->attributes());
        $this->product->taxes()->attach($this->taxes());

        $this->product->prices()->saveMany($this->prices());
        $this->product->additional_prices()->saveMany($this->additionalPrices());

        foreach ($this->properties() as $slug => $value) {
            PropertyValue::create([
                'product_id'  => $this->product->id,
                'property_id' => $this->property($slug)->id,
                'value'       => $value,
            ]);
        }

        foreach ($this->images() as $set) {
            $s                 = ImageSet::create([
                'name'        => $set['name'],
                'is_main_set' => $set['is_main_set'] ?? false,
                'product_id'  => $this->product->id,
            ]);
            $this->imageSets[] = $s;

            foreach ($set['images'] as $path) {
                $s->images()->create(['data' => $path]);
            }
        }

        foreach ($this->variants() as $variant) {
            $v = Variant::create([
                'product_id'   => $this->product->id,
                'image_set_id' => $variant['image_set_id'] ?? null,
                'stock'        => $variant['stock'],
                'name'         => $variant['name'],
                'price'        => $variant['price'] ?? null,
                'old_price'    => $variant['old_price'] ?? null,
                'published'    => true,
            ]);

            if (isset($variant['prices'])) {
                $variant['prices'] = collect($variant['prices'])->map(function ($price) {
                    $price['product_id'] = $this->product->id;

                    return $price;
                });
                $v->prices()->saveMany($variant['prices']);
            }

            foreach ($variant['properties'] as $slug => $value) {
                PropertyValue::create([
                    'variant_id'  => $v->id,
                    'product_id'  => $v->product_id,
                    'property_id' => $this->property($slug)->id,
                    'value'       => $value,
                ]);
            }
        }

        foreach ($this->customFields() as $customField) {
            $f = CustomField::create($customField);
            $this->product->custom_fields()->attach($f);
        }
    }

    protected function category($code)
    {
        return Category::whereCode($code)->firstOrFail();
    }

    protected function brand($slug)
    {
        return Brand::whereSlug($slug)->firstOrFail();
    }

    protected function property($slug)
    {
        return Property::whereSlug($slug)->firstOrFail();
    }
}