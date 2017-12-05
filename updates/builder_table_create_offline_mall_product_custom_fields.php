<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallProductCustomFields extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_custom_fields', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('name');
            $table->string('type')->default('text');
            $table->text('options')->nullable();
            $table->boolean('required')->default(false);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_product_custom_fields');
    }
}
