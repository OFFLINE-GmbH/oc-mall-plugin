<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallDiscountsProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discount_product', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('product_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_discount_product');
    }
}
