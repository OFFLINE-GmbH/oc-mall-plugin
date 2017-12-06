<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallCartProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_cart_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('cart_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned();
            $table->integer('quantity')->default(1);
            $table->integer('price')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_cart_product');
    }
}
