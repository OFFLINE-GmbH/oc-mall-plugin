<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallDiscountsCategories extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discounts_categories', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('category_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_discounts_categories');
    }
}