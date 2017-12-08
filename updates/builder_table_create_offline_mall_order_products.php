<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallOrderProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_order_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id');
            $table->integer('order_id');

            $table->string('name');
            $table->string('description');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('weight')->nullable();
            $table->integer('width')->nullable();
            $table->integer('length')->nullable();
            $table->integer('height')->nullable();
            $table->integer('total_weight');
            $table->integer('total_price');
            $table->boolean('stackable');
            $table->boolean('shippable');
            $table->boolean('taxable');
            $table->text('custom_fields');
            $table->text('taxes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_order_products');
    }
}
