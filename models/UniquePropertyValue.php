<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use DB;
use Model;
use Queue;
use Illuminate\Database\Query\Builder;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\UpdateUniquePropertyForCategory;

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

    public static function getForMultipleCategories(Collection $categories): Collection
    {
        $ids = self::selectRaw('MIN(property_value_id), value, index_value, property_id')
            ->whereIn('category_id', $categories->pluck('id'))
            ->groupBy('value', 'index_value', 'property_id')
            ->get('id')
            ->pluck('id');

        return self::whereIn('id', $ids)->get();
    }
    /**
     * Reset unique properties for provided category
     *
     * @param Category $category
     * @return void
     */
    public static function resetForCategory(Category $category): void
    {
        $records = self::getRawQueryForCategory($category)->get();

        DB::transaction(function ()  use ($category, $records) {
            $idsToLeave = [];
            foreach ($records as $record) {
                $uniquePropertyValue = self::where('property_id', $record->property_id)
                    ->where('category_id', $category->id)
                    ->where('value', $record->value)
                    ->where('index_value', $record->index_value)
                    ->first();

                if (!$uniquePropertyValue) {
                    $uniquePropertyValue = new UniquePropertyValue();
                    $uniquePropertyValue->property_id = $record->property_id;
                    $uniquePropertyValue->property_value_id = $record->id;
                    $uniquePropertyValue->category_id = $category->id;
                    $uniquePropertyValue->value = $record->value;
                    $uniquePropertyValue->index_value = $record->index_value;
                    $uniquePropertyValue->save();
                }

                $idsToLeave[] = $uniquePropertyValue->id;
            }

            // Cleanup all unique properties that were already deleted from the category
            $uniquePropertiesToRemove = self::where('category_id', $category->id)
                ->whereNotIn('id', $idsToLeave)
                ->get();

            foreach ($uniquePropertiesToRemove as $uniquePropertyToRemove) {
                $uniquePropertyToRemove->delete();
            }
        });
    }

    /**
     * Update unique property values using product
     *
     * @param Product $product
     * @return void
     */
    public static function updateUsingCategory(Category $category): void
    {
        Queue::push(UpdateUniquePropertyForCategory::class, ['id' => $category->id]);
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
        $product->loadMissing(['categories']);
        foreach ($product->categories as $category) {
            Queue::push(UpdateUniquePropertyForCategory::class, ['id' => $category->id]);
        }
    }

    public static function updateUsingPropertyGroup(PropertyGroup $propertyGroup): void
    {
        $propertyGroup->loadMissing(['categories']);
        foreach ($propertyGroup->categories as $category) {
            Queue::push(UpdateUniquePropertyForCategory::class, ['id' => $category->id]);
        }
    }

    public static function updateUsingProperty(Property $property): void
    {
        $property->loadMissing(['property_groups.categories']);
        foreach ($property->property_groups as $propertyGroup) {
            self::updateUsingPropertyGroup($propertyGroup);
        }
    }

    public static function updateUsingPropertyValue(PropertyValue $propertyValue): void
    {
        $propertyValue->loadMissing(['product.categories']);
        self::updateUsingProduct($propertyValue->product);
    }

    public static function getRawQueryForCategory(Category $category): Builder
    {
        return DB
            ::table('offline_mall_products')
            ->selectRaw(
                '
                MIN(offline_mall_property_values.id) AS id,
                offline_mall_property_values.value,
                offline_mall_property_values.index_value,
                offline_mall_property_values.property_id'
            )
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('offline_mall_products.published', true)
                        ->whereNull('offline_mall_product_variants.id');
                })->orWhere('offline_mall_product_variants.published', true);
            })
            ->where('offline_mall_category_product.category_id', $category->id)
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->whereNull('offline_mall_products.deleted_at')
            ->where('offline_mall_property_values.value', '<>', '')
            ->whereNotNull('offline_mall_property_values.value')
            ->groupBy(
                'offline_mall_property_values.value',
                'offline_mall_property_values.index_value',
                'offline_mall_property_values.property_id'
            )
            ->leftJoin(
                'offline_mall_product_variants',
                'offline_mall_products.id',
                '=',
                'offline_mall_product_variants.product_id'
            )
            ->leftJoin(
                'offline_mall_category_product',
                'offline_mall_products.id',
                '=',
                'offline_mall_category_product.product_id'
            )
            ->join(
                'offline_mall_property_values',
                'offline_mall_products.id',
                '=',
                'offline_mall_property_values.product_id'
            );
    }
}
