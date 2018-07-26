<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallProductCustomField extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_custom_field', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('custom_field_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_product_custom_field');
    }
}
