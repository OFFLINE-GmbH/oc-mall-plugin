<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallShippingMethodRates extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_shipping_method_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('shipping_method_id')->unsigned();
            $table->integer('from_weight')->unsigned()->default(0);
            $table->integer('to_weight')->unsigned()->nullable();
            $table->integer('price')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_shipping_method_rates');
    }
}
