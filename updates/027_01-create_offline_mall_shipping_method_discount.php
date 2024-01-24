<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallShippingMethodDiscount extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_shipping_method_discount', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('shipping_method_id')->unsigned();
            $table->integer('discount_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_shipping_method_discount');
    }
}
