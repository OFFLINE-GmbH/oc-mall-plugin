<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use DB;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\UniquePropertyValue;
use Schema;

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
                $table->index(['property_id', 'category_id'], 'idx_property_values_categories');
            }
        });

        DB::transaction(function () {
            foreach (Category::all() as $category) {
                UniquePropertyValue::resetForCategory($category);
            }
        });
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
};
