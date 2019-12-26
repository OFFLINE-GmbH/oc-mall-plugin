<?php

namespace OFFLINE\Mall\Classes\Demo\Products;

use October\Rain\Support\Arr;
use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\CategoryReview;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\Price;
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

    abstract protected function prices(): array;

    abstract protected function categories(): array;

    protected function additionalPrices(): array
    {
        return [];
    }

    protected function reviews(): array
    {
        return [];
    }

    public function create()
    {
        $this->product = Product::create($this->attributes());
        $this->product->taxes()->attach($this->taxes());

        $this->product->prices()->saveMany($this->prices());
        $this->product->categories()->attach($this->categories());
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
                'published'    => true,
            ]);

            if (isset($variant['prices'])) {
                $variant['prices'] = collect($variant['prices'])->map(function ($price) {
                    $price['product_id'] = $this->product->id;

                    return $price;
                });
                $v->prices()->saveMany($variant['prices']);
            }

            if (isset($variant['old_price'])) {
                foreach ($variant['old_price'] as $currency => $price) {
                    Price::create([
                        'currency_id'       => Currency::resolve($currency)->id,
                        'price'             => $price,
                        'priceable_id'      => $v->id,
                        'priceable_type'    => 'mall.variant',
                        'price_category_id' => 1,
                    ]);
                }
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
            $prices = $customField['price'];
            unset($customField['price']);

            $f = CustomField::create($customField);
            collect($prices)->map(function ($price, $currency) use ($f) {
                return new Price([
                    'currency_id'    => Currency::resolve($currency)->id,
                    'price'          => $price,
                    'priceable_id'   => $f->id,
                    'priceable_type' => 'mall.custom_field',
                ]);
            });
            $this->product->custom_fields()->attach($f);
        }

        foreach ($this->reviews() as $data) {
            $review = $data['review'];

            $review->approved_at = now();
            $review->product_id  = $this->product->id;
            $review->variant_id  = Arr::random($this->product->fresh('variants')->variants->toArray())['id'];
            $review->save();

            foreach ($data['category_reviews'] ?? [] as $categoryId => $rating) {
                $cr = new CategoryReview([
                    'review_id'          => $review->id,
                    'review_category_id' => $categoryId + 1,
                    'rating'             => $rating,
                    'approved_at'        => now(),
                ]);
                $cr->save();
            }
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
