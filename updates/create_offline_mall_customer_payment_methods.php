<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallCustomerPaymentMethods extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_customer_payment_methods', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullabe();
            $table->integer('customer_id');
            $table->integer('payment_method_id');
            $table->boolean('is_default')->default(0);
            $table->mediumText('data')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::table('offline_mall_orders', function ($table) {
            $table->integer('customer_payment_method_id')->nullable();
        });
        Schema::table('offline_mall_carts', function ($table) {
            $table->integer('customer_payment_method_id')->nullable();
        });
        Schema::table('offline_mall_customers', function ($table) {
            $table->string('stripe_customer_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_customer_payment_methods');
        Schema::table('offline_mall_orders', function ($table) {
            $table->dropColumn(['customer_payment_method_id']);
        });
        Schema::table('offline_mall_carts', function ($table) {
            $table->dropColumn(['customer_payment_method_id']);
        });
        Schema::table('offline_mall_customers', function ($table) {
            $table->dropColumn(['stripe_customer_id']);
        });
    }
}
