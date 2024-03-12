<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallCartDiscount extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_cart_discount', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('cart_id')->unsigned();
            $table->integer('discount_id')->unsigned();

            if ( ! app()->runningUnitTests()) {
                $table->index(['cart_id', 'discount_id'], 'idx_cart_discount_pivot');
            }
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_cart_discount');
    }
}
