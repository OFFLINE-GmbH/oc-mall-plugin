<?php namespace OFFLINE\Mall\Updates;

use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallPropertyValues extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_property_values', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('variant_id')->unsigned()->nullable();
            $table->integer('property_id');
            $table->text('value')->nullable();
            $table->text('index_value')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            if ( ! app()->runningUnitTests()) {
                // Use MySQL specific index names only when running on MySQL.
                if (DB::connection()->getDriverName() === 'mysql') {
                    $table->index([DB::raw('value(191)')], 'idx_property_value_value');
                    $table->index([DB::raw('index_value(191)')], 'idx_property_value_index_value');
                }

                $table->index(['product_id', 'variant_id'], 'idx_property_value_product_variant');
                $table->index('product_id', 'idx_property_value_product');
                $table->index('variant_id', 'idx_property_value_variant');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_property_values');
    }
}
