<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallOrders extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_orders', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('order_number')->nullable()->unique();
            $table->string('invoice_number')->nullable();
            $table->string('currency')->nullable();
            $table->string('payment_status');
            $table->string('order_status');
            $table->boolean('shipping_address_same_as_billing')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('custom_fields')->nullable();
            $table->text('shipping')->nullable();
            $table->text('taxes')->nullable();
            $table->text('discounts')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('payment_data')->nullable();
            $table->text('payment_id')->nullable();
            $table->integer('shipping_pre_taxes')->nullable();
            $table->integer('shipping_taxes')->nullable();
            $table->integer('total_shipping')->nullable();
            $table->integer('product_taxes')->nullable();
            $table->integer('total_product')->nullable();
            $table->integer('total_taxes')->nullable();
            $table->integer('total_pre_taxes')->nullable();
            $table->integer('total_post_taxes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('credit_card_last4_digits', 4)->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_type')->nullable();
            $table->string('lang');
            $table->integer('total_weight')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('customer_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_orders');
    }
}
