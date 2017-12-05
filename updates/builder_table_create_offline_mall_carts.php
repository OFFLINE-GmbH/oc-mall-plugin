<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallCarts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_carts', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('session_id')->nullable();
            $table->integer('user_id')->nullable()->unsigned();
            $table->integer('shipping_method_id')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_carts');
    }
}
