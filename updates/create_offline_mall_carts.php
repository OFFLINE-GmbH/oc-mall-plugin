<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallCarts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_carts', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('session_id')->nullable();
            $table->integer('customer_id')->nullable()->unsigned();
            $table->integer('shipping_address_id')->nullable()->unsigned();
            $table->integer('billing_address_id')->nullable()->unsigned();
            $table->integer('shipping_method_id')->nullable()->unsigned();
            $table->integer('payment_method_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            if ( ! app()->runningUnitTests()) {
                $table->index('session_id', 'idx_cart_session_id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_carts');
    }
}
