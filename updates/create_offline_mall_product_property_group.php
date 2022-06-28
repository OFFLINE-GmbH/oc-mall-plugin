<?php namespace StudioAzura\Tweaks\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateOfflineMallProductPropertyGroup extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_property_group', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('property_group_id')->unsigned();
            $table->integer('relation_sort_order')->unsigned()->nullable();
            $table->timestamps();

            if ( ! app()->runningUnitTests()) {
                $table->index(['product_id', 'property_group_id'], 'idx_property_group_pivot');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_product_property_group');
    }
}
