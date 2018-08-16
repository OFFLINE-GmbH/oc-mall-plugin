<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallShippingMethods extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_shipping_methods', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->text('price');
            $table->integer('sort_order')->unsigned()->nullable();
            $table->integer('guaranteed_delivery_days')->nullable();
            $table->text('available_below_total')->nullable();
            $table->text('available_above_total')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_shipping_methods');
    }
}
