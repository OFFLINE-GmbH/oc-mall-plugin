<?php

namespace OFFLINE\Mall\Classes\Demo\Products;


use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\Product;
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

    public function create()
    {
        $this->product = Product::create($this->attributes());
        $this->product->taxes()->attach($this->taxes());

        foreach ($this->properties() as $slug => $value) {
            PropertyValue::create([
                'describable_id'   => $this->product->id,
                'describable_type' => Product::MORPH_KEY,
                'property_id'      => $this->property($slug)->id,
                'value'            => $value,
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
            foreach ($variant['properties'] as $slug => $value) {
                PropertyValue::create([
                    'describable_id'   => $v->id,
                    'describable_type' => Variant::MORPH_KEY,
                    'property_id'      => $this->property($slug)->id,
                    'value'            => $value,
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