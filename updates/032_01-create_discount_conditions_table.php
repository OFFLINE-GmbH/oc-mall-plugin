<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateDiscountConditionsTable extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discount_conditions', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('discount_id');
            $table->string('trigger');
            $table->string('code')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('customer_group_id')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('discount_id')
                ->references('id')
                ->on('offline_mall_discounts')
                ->onDelete('cascade');
        });

        Schema::create('offline_mall_discount_condition_shipping_method', function ($table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('condition_id');
            $table->unsignedInteger('shipping_method_id');
            $table->primary(['condition_id', 'shipping_method_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_discount_condition_shipping_method');
        Schema::dropIfExists('offline_mall_discount_conditions');
    }
}
