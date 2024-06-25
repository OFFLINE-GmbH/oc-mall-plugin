<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use October\Rain\Database\Traits\Validation;

class UniquePropertyValue extends Model
{
    use Validation;

    /**
     * @var string table name
     */
    public $table = 'offline_mall_unique_property_values';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'property_value_id' => 'required|exists:offline_mall_property_values,id',
        'property_id' => 'required|exists:offline_mall_properties,id',
        'category_id' => 'required|exists:offline_mall_categories,id',
        'value' => 'required',
        'index_value' => 'required',
    ];

    public $timestamps = false;

    public $fillable = [
        'property_value_id',
        'property_id',
        'value',
        'index_value',
        'category_id',
    ];

    public $belongsTo = [
        'property' => [Property::class, 'deleted' => true],
        'property_value' => [PropertyValue::class, 'deleted' => true],
        'category'  => [Category::class],
    ];

    /**
     * Update unique property values using category
     * It's looking for all products in the category and updating uniques just for them
     *
     * @param Category $category
     * @return void
     */
    public static function updateUsingCategory(Category $category): void
    {
        $products = Product::whereHas('categories', function ($q) use ($category) {
            $q->where($category->getTable() . '.id', $category->id);
        })->with([
            'property_values',
            'variants.property_values',
        ])->get();

        $uniqueIdsToLeave = [];
        foreach ($products as $product) {
            $propertyValues = $product->property_values ?? [];
            foreach ($propertyValues as $propertyValue) {
                $uniquePropertyValue = self::whereOrCreate([
                    'property_id' => $propertyValue->property_id,
                    'property_value_id' => $propertyValue->id,
                    'category_id' => $category->id,
                    'value' => $propertyValue->value,
                    'index_value' => $propertyValue->index_value,
                ]);

                $uniqueIdsToLeave[] = $uniquePropertyValue->id;
            }

            $variants = $product->variants ?? [];
            foreach ($variants as $variant) {
                $propertyValues = $variant->property_values ?? [];
                foreach ($propertyValues as $propertyValue) {
                    $uniquePropertyValue = self::whereOrCreate([
                        'property_id' => $propertyValue->property_id,
                        'property_value_id' => $propertyValue->id,
                        'category_id' => $category->id,
                        'value' => $propertyValue->value,
                        'index_value' => $propertyValue->index_value,
                    ]);

                    $uniqueIdsToLeave[] = $uniquePropertyValue->id;
                }
            }
        }

        // Cleanup all properties that were already deleted from the category
        $productPropertyValuesIds = PropertyValue::whereIn('product_id', $products->pluck('id'))
            ->get('id')
            ->pluck('id');

        UniquePropertyValue::whereIn('property_value_id', $productPropertyValuesIds)
            ->whereNotIn('id', $uniqueIdsToLeave)
            ->delete();
    }

    /**
     * Update unique property values using product
     * It's looking for all categories that the product is in and updates it
     *
     * @param Product $product
     * @return void
     */
    public static function updateUsingProduct(Product $product): void
    {
        $product->loadMissing([
            'categories',
            'property_values',
            'variants.property_values'
        ]);

        $uniqueIdsToLeave = [];
        foreach ($product->categories as $category) {
            $propertyValues = $product->property_values ?? [];
            foreach ($propertyValues as $propertyValue) {
                $uniquePropertyValue = self::whereOrCreate([
                    'property_id' => $propertyValue->property_id,
                    'property_value_id' => $propertyValue->id,
                    'category_id' => $category->id,
                    'value' => $propertyValue->value,
                    'index_value' => $propertyValue->index_value,
                ]);

                $uniqueIdsToLeave[] = $uniquePropertyValue->id;

                $variants = $product->variants ?? [];
                foreach ($variants as $variant) {
                    $propertyValues = $variant->property_values ?? [];
                    foreach ($propertyValues as $propertyValue) {
                        $uniquePropertyValue = self::whereOrCreate([
                            'property_id' => $propertyValue->property_id,
                            'property_value_id' => $propertyValue->id,
                            'category_id' => $category->id,
                            'value' => $propertyValue->value,
                            'index_value' => $propertyValue->index_value,
                        ]);

                        $uniqueIdsToLeave[] = $uniquePropertyValue->id;
                    }
                }
            }
        }

        // Cleanup all properties that the product lost on the way
        $productPropertyValuesIds = PropertyValue::where('product_id', $product->id)
            ->get('id')
            ->pluck('id');

        UniquePropertyValue::whereIn('property_value_id', $productPropertyValuesIds)
            ->whereNotIn('id', $uniqueIdsToLeave)
            ->delete();
    }

    public static function updateUsingPropertyGroup(PropertyGroup $propertyGroup): void
    {
        $propertyGroup->loadMissing(['categories']);
        foreach ($propertyGroup->categories as $category) {
            self::updateUsingCategory($category);
        }
    }

    public static function updateUsingProperty(Property $property): void
    {
        foreach ($property->property_groups as $propertyGroup) {
            self::updateUsingPropertyGroup($propertyGroup);
        }
    }

    public static function updateUsingPropertyValue(PropertyValue $propertyValue): void
    {
        self::updateUsingProduct($propertyValue->product);
    }

    /**
     * Helper method, similar to firstOrCreate, but
     * we're not using property_value_id when looking for model but we're storing it
     *
     * @param array $data
     * @return self
     */
    public static function whereOrCreate(array $data): self
    {
        $uniquePropertyValue = UniquePropertyValue::where('property_id', $data['property_id'])
            ->where('category_id', $data['category_id'])
            ->where('value', $data['value'])
            ->where('index_value', $data['index_value'])
            ->first();

        if (!$uniquePropertyValue) {
            $uniquePropertyValue = new UniquePropertyValue();
            $uniquePropertyValue->property_id = $data['property_id'];
            $uniquePropertyValue->property_value_id = $data['property_value_id'];
            $uniquePropertyValue->category_id = $data['category_id'];
            $uniquePropertyValue->value = $data['value'];
            $uniquePropertyValue->index_value = $data['index_value'];
            $uniquePropertyValue->save();
        }

        return $uniquePropertyValue;
    }
}
