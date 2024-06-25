<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use DB;
use Schema;
use OFFLINE\Mall\Models\Category;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Models\UniquePropertyValue;

/**
 * CreateUniquePropertyValuesTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
class CreateUniquePropertyValuesTable_031_01 extends Migration
{
    /**
     * Install Migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_mall_unique_property_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('property_value_id')->unsigned();
            $table->integer('property_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->text('value')->nullable();
            $table->text('index_value')->nullable();

            if (!app()->runningUnitTests()) {
                // We're using all four columns in where clauses, thus index on all of them
                $table->index(['property_id', 'category_id', 'value', 'index_value'], 'idx_property_values_categories');
            }
        });

        foreach (Category::all() as $category) {
            $records = $this->oldQuery($category)->get();

            foreach ($records as $record) {
                $uniquePropertyValue = new UniquePropertyValue();
                $uniquePropertyValue->category_id = $category->id;
                $uniquePropertyValue->property_value_id = $record->id;
                $uniquePropertyValue->property_id = $record->property_id;
                $uniquePropertyValue->value = $record->value;
                $uniquePropertyValue->index_value = $record->index_value;
                $uniquePropertyValue->save();
            }
        }
    }

    /**
     * Uninstall Migration
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offline_mall_unique_property_values');
    }

    public function oldQuery($category)
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
};
