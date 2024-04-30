<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallProductAccessory extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_accessory', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('accessory_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_product_accessory');
    }
}
