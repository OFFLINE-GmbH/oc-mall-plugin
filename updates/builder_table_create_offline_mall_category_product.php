<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallCategoryProduct extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_category_product', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('category_id')->unsigned();
            $table->integer('product_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_category_product');
    }
}
