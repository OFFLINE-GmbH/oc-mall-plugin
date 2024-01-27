<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallCategoryProductSortOrder extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_category_product_sort_order', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('sort_order')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_category_product_sort_order');
    }
}
